CREATE KEYSPACE IF NOT EXISTS metadata WITH replication = {'class': 'SimpleStrategy', 'replication_factor': 1};
USE "metadata";

/* Metadata Entities */
DROP TABLE IF EXISTS entities;
CREATE TABLE entities (
	entityid text,
    feed text,
    enabled boolean,
    verification text,	-- JSON Structure
	metadata text,      -- JSON Structure
    uimeta text,        -- JSON Structure
	reg text,
	created timestamp,
	updated timestamp,

    logo blob,
    logo_etag text,
    logo_updated timestamp,

    PRIMARY KEY ((entityid), feed)
);
