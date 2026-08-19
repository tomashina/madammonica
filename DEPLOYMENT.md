# Madam Monica deployment

## Layout

- Repository/server root: `/home/amds/balidoo.agmedia.rocks`
- Public web root: `/home/amds/balidoo.agmedia.rocks/upload`
- Local URL: `https://madammonica.test`

## Persistent environment files

The following paths are intentionally not tracked and must remain on the server:

- `upload/config.php`
- `upload/admin/config.php`
- `upload/.htaccess`
- `upload/image/catalog/`
- `storage/cache/`, `storage/logs/`, `storage/modification/`, `storage/session/`
- `storage/vendor/`

Safe templates are available as `upload/config-example.php` and
`upload/admin/config-example.php`.

## Existing-server Git adoption

Do not delete or replace the existing project directory. Back it up first, then
attach Git to the existing working tree:

```bash
cd /home/amds/balidoo.agmedia.rocks
git init
git symbolic-ref HEAD refs/heads/main
git remote add origin https://github.com/tomashina/madammonica.git
git fetch origin main
git reset --mixed origin/main
git branch --set-upstream-to=origin/main main
git status
```

The mixed reset adopts the remote commit without overwriting the existing
working files. Review any tracked differences reported by `git status` before
the first deployment.

For subsequent deployments:

```bash
cd /home/amds/balidoo.agmedia.rocks
git pull --ff-only
composer install --no-dev --optimize-autoloader
```

After deploying OpenCart code, refresh **Extensions > Modifications** and clear
the application/theme cache from the administration interface.
