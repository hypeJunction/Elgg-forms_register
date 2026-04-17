import { Page } from '@playwright/test';
import mysql from 'mysql2/promise';

const DB_CONFIG = {
  host: process.env.ELGG_DB_HOST || 'db',
  port: Number(process.env.ELGG_DB_PORT || 3306),
  user: process.env.ELGG_DB_USER || 'elgg',
  password: process.env.ELGG_DB_PASS || 'elgg',
  database: process.env.ELGG_DB_NAME || 'elgg',
};

export async function queryDb(sql: string, params: any[] = []): Promise<any[]> {
  const conn = await mysql.createConnection(DB_CONFIG);
  const [rows] = await conn.execute(sql, params);
  await conn.end();
  return rows as any[];
}

/**
 * In Elgg 4.x, user fields (username, email) are stored in elgg_metadata,
 * not in a separate elgg_users_entity table.
 */
export async function getUserByUsername(username: string): Promise<any | null> {
  const rows = await queryDb(
    `SELECT e.guid, e.type, e.subtype,
            MAX(CASE WHEN m.name = 'username' THEN m.value END) AS username,
            MAX(CASE WHEN m.name = 'email' THEN m.value END) AS email
     FROM elgg_entities e
     JOIN elgg_metadata m ON m.entity_guid = e.guid
     WHERE e.type = 'user'
       AND e.guid = (SELECT entity_guid FROM elgg_metadata
                     WHERE name = 'username' AND value = ? LIMIT 1)
     GROUP BY e.guid`,
    [username]
  );
  return rows[0] || null;
}

export async function getUserMetadata(guid: number, name: string): Promise<string | null> {
  const rows = await queryDb(
    `SELECT value FROM elgg_metadata WHERE entity_guid = ? AND name = ?`,
    [guid, name]
  );
  return rows[0]?.value ?? null;
}

export async function deleteUserByUsername(username: string): Promise<void> {
  const user = await getUserByUsername(username);
  if (!user) return;
  // Clean up in dependency order (elgg_users_sessions stores no guid in 4.x)
  await queryDb(`DELETE FROM elgg_metadata WHERE entity_guid = ?`, [user.guid]);
  await queryDb(`DELETE FROM elgg_entity_relationships WHERE guid_one = ? OR guid_two = ?`, [user.guid, user.guid]);
  await queryDb(`DELETE FROM elgg_private_settings WHERE entity_guid = ?`, [user.guid]);
  await queryDb(`DELETE FROM elgg_annotations WHERE entity_guid = ?`, [user.guid]);
  await queryDb(`DELETE FROM elgg_entities WHERE guid = ?`, [user.guid]);
}

export async function gotoRegister(page: Page): Promise<void> {
  await page.goto('/register');
}
