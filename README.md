# Laravel API Project

## Local Development with Nginx Proxy Manager

This project comes with a pre-configured Nginx Proxy Manager setup for local domains.

### Setting Up Your Local Environment

1. Start the Docker containers:
   ```bash
   docker-compose up -d
   ```

2. Add the following entry to your hosts file:
   ```
   127.0.0.1  api.local
   ```
   - On Linux/Mac: Edit `/etc/hosts` with `sudo nano /etc/hosts`
   - On Windows: Edit `C:\Windows\System32\drivers\etc\hosts` as Administrator

3. Access your application at: http://api.local

The Nginx Proxy Manager is already configured to route requests from api.local to your Laravel application.

### Nginx Proxy Manager Admin (if needed)

If you need to modify the proxy settings or add additional domains:

1. Access the admin panel at: http://localhost:81
   - Default Email: admin@example.com
   - Default Password: changeme

2. You'll be prompted to change your password on first login.

## Running Artisan Commands

To run artisan commands using the container:

```bash
docker exec -it php php artisan <command>
```

For example:
```bash
docker exec -it php php artisan migrate
```
