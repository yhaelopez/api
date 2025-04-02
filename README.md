# Laravel API Project

## Nginx Proxy Manager Setup

This project uses Nginx Proxy Manager for managing local domains and SSL.

### Accessing the Admin Panel

1. After starting the containers with `docker-compose up -d`, access the Nginx Proxy Manager admin panel at:
   - URL: http://localhost:81
   - Default Email: admin@example.com
   - Default Password: changeme
   
2. After logging in for the first time, you'll be prompted to change your password.

### Setting Up a Local Domain

1. Add an entry to your hosts file:
   ```
   127.0.0.1  api.local
   ```
   - On Linux/Mac: Edit `/etc/hosts`
   - On Windows: Edit `C:\Windows\System32\drivers\etc\hosts`

2. In the NPM admin panel, go to "Hosts" > "Proxy Hosts" and click "Add Proxy Host"

3. Enter the following details:
   - Domain: api.local
   - Scheme: http
   - Forward Hostname/IP: nginx
   - Forward Port: 80
   - Check "Block Common Exploits"

4. (Optional) SSL Tab:
   - Select "Request a new SSL Certificate" if you want HTTPS for local development
   - Check "Force SSL" to redirect HTTP to HTTPS

5. Save and your local domain is ready to use!

## Running Artisan Commands

To run artisan commands using the container:

```bash
docker exec -it php php artisan <command>
```

For example:
```bash
docker exec -it php php artisan migrate
```
