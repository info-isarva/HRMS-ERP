CREATE TEMPORARY TABLE debug_permissions AS
SELECT 
    id,
    name,
    display_name,
    route_name,
    route_names,
    CASE 
        WHEN route_names IS NOT NULL AND route_names != '' THEN JSON_UNQUOTE(JSON_EXTRACT(route_names, '$'))
        ELSE route_name
    END as all_routes
FROM permissions 
WHERE name LIKE '%employees%';

SELECT * FROM debug_permissions;