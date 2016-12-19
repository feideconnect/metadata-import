
CREATE KEYSPACE IF NOT EXISTS metadata WITH replication = {'class': 'SimpleStrategy', 'replication_factor': 1};
USE "metadata";

/* Metadata Entities */
DROP TABLE IF EXISTS entities;
CREATE TABLE entities (
	entityid text,
    feed text,
	metadata text,	-- JSON Structure
	country text,
	created timestamp,
	updated timestamp,
    PRIMARY KEY ((entityid), feed)
);
