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

## Twilio Mock Integration

This project includes a Twilio Mock setup using Prism for testing SMS functionality without hitting the real Twilio API.

### Setup Instructions

1. **Download Twilio OpenAPI spec:**
   ```bash
   composer twilio:spec
   ```

2. **Start the Twilio Mock container:**
   ```bash
   ./vendor/bin/sail up -d twilio-mock
   ```

3. **Start all services:**
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **Clear configuration cache:**
   ```bash
   php artisan config:clear
   ```

5. **Run the test:**
   ```bash
   php artisan test --filter TwilioMockTest
   ```

### Testing SMS Functionality

You can test the SMS functionality by visiting:
```
http://api.local/_dev/sms
```

Or with a custom phone number:
```
http://api.local/_dev/sms?to=+1234567890
```

The endpoint will return a JSON response with `ok: true` and SMS details when using the mock.

### Configuration

The Twilio configuration is located in `config/services.php`. The mock is enabled by default with `TWILIO_MOCK=true` in your environment variables.

- `TWILIO_SID`: Your Twilio Account SID (defaults to test value)
- `TWILIO_TOKEN`: Your Twilio Auth Token (defaults to test value)
- `TWILIO_FROM`: The phone number to send from (defaults to Twilio test number)
- `TWILIO_MOCK`: Enable/disable mock mode (defaults to true)
- `TWILIO_MOCK_BASE`: Mock server URL (defaults to http://twilio-mock:4010/)
