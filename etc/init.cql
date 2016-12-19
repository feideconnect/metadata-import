
CREATE KEYSPACE IF NOT EXISTS metadata WITH replication = {'class': 'SimpleStrategy', 'replication_factor': 1};
USE "metadata";

/* Metadata Entities */
DROP TABLE IF EXISTS entities;
CREATE TABLE entities (
	id text PRIMARY KEY,
	metadata text,	-- JSON Structure
	country text,
  feed text,
	created timestamp,
	updated timestamp
);


/*
CREATE INDEX user_userid_sec_idx ON entities(userid_sec);
*/
