--  Privileges of the gallery administration for drago-ex/project-permission
-- ----------------------------------------------------------------------------
-- Imported by hand into the tables of project-permission (see the README), so roles can be given these privileges
-- in the administration. They are what the Authorization trait checks: a page needs "{action}-read",
-- the preview of an image "gallery-read" (once the presenter marks it read-only, see the README),
-- the other signals of the component (upload, delete) "gallery-write".
INSERT IGNORE INTO resources (resource, privilege, description) VALUES
    ('Backend:Gallery', 'default-read', 'View the gallery'),
    ('Backend:Gallery', 'gallery-read', 'Preview images in the gallery'),
    ('Backend:Gallery', 'gallery-write', 'Manage the gallery');
