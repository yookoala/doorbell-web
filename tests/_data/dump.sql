CREATE TABLE IF NOT EXISTS doorbell_rings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ring_time INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS api_keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    api_key TEXT NOT NULL,
    revoked_at INTEGER
);

-- Add a test API key
INSERT INTO api_keys (id, api_key) VALUES (1, 'test-api-key');
