-- Utility functions for database management

-- Function to disable foreign key constraints temporarily
CREATE OR REPLACE FUNCTION disable_foreign_keys() RETURNS VOID AS $$
BEGIN
  EXECUTE 'SET session_replication_role = replica;';
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Function to re-enable foreign key constraints
CREATE OR REPLACE FUNCTION enable_foreign_keys() RETURNS VOID AS $$
BEGIN
  EXECUTE 'SET session_replication_role = DEFAULT;';
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Function to truncate a table
CREATE OR REPLACE FUNCTION truncate_table(table_name TEXT) RETURNS VOID AS $$
BEGIN
  EXECUTE format('TRUNCATE TABLE %I CASCADE', table_name);
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Grant execute permissions to anon role
GRANT EXECUTE ON FUNCTION disable_foreign_keys() TO anon;
GRANT EXECUTE ON FUNCTION enable_foreign_keys() TO anon;
GRANT EXECUTE ON FUNCTION truncate_table(TEXT) TO anon;
