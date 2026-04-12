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

export async function getUserByUsername(username: string): Promise<any | null> {
  const rows = await queryDb(
    `SELECT e.guid, e.type, e.subtype, u.username, u.email
     FROM elgg_entities e
     JOIN elgg_users_entity u ON e.guid = u.guid
     WHERE u.username = ?`,
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
  // Soft cleanup; production cleanup should go through Elgg's user delete
  await queryDb(`DELETE FROM elgg_metadata WHERE entity_guid = ?`, [user.guid]);
  await queryDb(`DELETE FROM elgg_users_entity WHERE guid = ?`, [user.guid]);
  await queryDb(`DELETE FROM elgg_entities WHERE guid = ?`, [user.guid]);
}

export async function setPluginSetting(
  pluginId: string,
  name: string,
  value: string
): Promise<void> {
  const rows = await queryDb(
    `SELECT e.guid FROM elgg_entities e
     JOIN elgg_metadata m ON m.entity_guid = e.guid
     WHERE e.subtype = 'plugin' AND m.name = 'title' AND m.value = ?`,
    [pluginId]
  );
  if (!rows[0]) return;
  const guid = rows[0].guid;
  await queryDb(
    `INSERT INTO elgg_private_settings (entity_guid, name, value)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE value = VALUES(value)`,
    [guid, `plugin:user_setting:${pluginId}:${name}`, value]
  );
  // Also set via generic private setting path
  await queryDb(
    `INSERT INTO elgg_private_settings (entity_guid, name, value)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE value = VALUES(value)`,
    [guid, name, value]
  );
}

export async function gotoRegister(page: Page): Promise<void> {
  await page.goto('/register');
}
