# Notification Service
This project implements a notification service capable of sending notifications via multiple channels, including SMS, email, and push notifications. The service is designed with flexibility, failover support, and configuration-driven features, making it suitable for various messaging requirements.

## Features
* Multi-channel Notifications: Supports sending notifications via SMS, email, and push notifications.
* Provider Abstraction: Abstracts the use of different messaging providers (e.g., Twilio, AWS SES) to allow easy integration and failover between providers.
* Failover Support: Automatically switches to a secondary provider if the primary one fails.
* Configuration-Driven: Enables/disables channels and configures providers through simple configuration files.
* Throttling: Limits the number of notifications sent to a user within a specified time frame.
* Usage Tracking: Tracks which notifications were sent, when, and to whom.
* Domain-Driven Design (DDD): The project is structured following DDD principles.

## Project structure
````
src/
├── NotificationPublisher/
│   ├── Application/
│   │   ├── Command/
│   │   │   └── SendNotificationCommand.php
│   │   ├── CommandHandler/
│   │   │   └── SendNotificationCommandHandler.php
│   │   └── Factory/
│   │       └── NotificationProviderFactory.php
│   ├── Domain/
│   │   ├── Entity/
│   │   │   └── Notification.php
│   │   ├── Exception/
│   │   │   └── NotificationSendException.php
│   │   ├── Provider/
│   │   │   └── NotificationProviderInterface.php
│   │   └── Repository/
│   │       └── NotificationRepositoryInterface.php
│   └── Infrastructure/
│       ├── Persistence/
│       │   └── DoctrineNotificationRepository.php
│       ├── Provider/
│       │   ├── AwsMailNotificationProvider.php
│       │   ├── AwsSmsNotificationProvider.php
│       │   ├── PushyNotificationProvider.php
│       │   └── TwilioSmsNotificationProvider.php
│   └── UserInterface/
│       └── CLI/
│           └── SendNotificationConsoleCommand.php
tests/
├── Unit/
│   └── NotificationPublisher/
│       ├── Application/
│       │   └── CommandHandler/
│       │       └── SendNotificationCommandHandlerTest.php
│       └── Infrastructure/
│           └── Provider/
│               ├── AwsMailNotificationProviderTest.php
│               ├── AwsSmsNotificationProviderTest.php
│               ├── PushyNotificationProviderTest.php
│               └── TwilioSmsNotificationProviderTest.php
└── Integration/
    └── NotificationPublisher/
        └── Application/
            └── CommandHandler/
                └── SendNotificationCommandHandlerTest.php

````

## Getting Started
1. ### Build and start the containers 
    `docker-compose build ` + `docker-compose up -d`
2. ### Set up the environment
   Copy the .env file and set up the required environment variables for database connection, Twilio, AWS SES, and Pushy.

    ` cp .env.example .env `

3. ### Go to php container shell: `docker-compose exec php sh`
   1. Install dependencies: `composer install`
   2. Run database migrations: `php bin/console doctrine:migrations:migrate`

## Usage

### Sending a Notification
To send a notification, you can use the Symfony CLI command:

`app:send-notification <recipient> <content> <channel> [<limitPerHour>]`
#### for example: 
`php bin/console app:send-notification "This is a test message" "+1234567890" sms`

This command sends an SMS notification to the specified phone number.

### Configuration
All providers and channels can be configured via the config/packages/parameters.yaml file or directly within your service container configuration.

```
parameters:
    notification_channels:
        sms:
            enabled: true
            providers: [twilio, aws_sns]
        email:
            enabled: true
            providers: [aws_ses, sendgrid]

```

## Testing
### Running Tests
To run the tests:

` docker-compose exec php sh -c "php bin/phpunit"`

## Architecture and Design Decisions
* Domain-Driven Design (DDD): The project is structured around DDD principles, separating concerns into different layers like Domain, Application, and Infrastructure.
* Provider Abstraction: The use of NotificationProviderInterface allows for easy extension and addition of new providers without modifying existing code.
* Failover and Resilience: The service is designed to switch providers in case of failure, ensuring that notifications are sent reliably.
* Configuration-Driven: All channels and providers can be configured without touching the core code, allowing for flexibility and easier maintenance.

## Future Improvements
* Enhance Throttling: Implement more sophisticated throttling mechanisms that consider different channels separately.
* Add More Providers: Extend support for additional providers like Facebook Messenger or WhatsApp.
* Expand Testing: Increase test coverage, particularly for edge cases and error handling.
