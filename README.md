# Money Management API

A robust RESTful API for personal finance management built with Laravel. This API provides comprehensive functionality for tracking income, expenses, budgets, and savings goals with built-in notification systems.

## Features

### Core Features
- JWT-based authentication
- Budget management and tracking
- Income and expense tracking
- Savings goals with milestone tracking
- Custom transaction categories
- Comprehensive notification system

### Key Highlights
- Real-time budget tracking
- Milestone notifications for savings goals
- Recurring transaction support
- Customizable notification preferences
- Detailed financial reporting
- Category-based transaction organization

## Technical Stack

- PHP 8.2+
- Laravel 10.x
- PostgreSQL/MySQL
- JWT Authentication
- OpenAPI Documentation

## Prerequisites

- PHP >= 8.2
- Composer
- PostgreSQL or MySQL
- PHP Extensions:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/money-manager.git
```
2. Install dependencies:
```bash
composer install
```
3. Configure environment variables:
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```
4. Configure database in `.env`:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=money_manager
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations:
```bash
php artisan migrate
```

6. Start the development server:
```bash
php artisan serve
```

## Testing

Run the test suite:
```bash
php artisan test
```

Run specific test files:
```bash
php artisan test --testsuite=Feature
```

## Code Structure
app/
├── Console/
│ └── Commands/ # Console commands for scheduled tasks
├── Http/
│ ├── Controllers/ # API controllers
│ ├── Requests/ # Form requests and validation
│ └── Resources/ # API resources/transformers
├── Models/ # Eloquent models
├── Notifications/ # Notification classes
└── Policies/ # Authorization policies
database/
├── factories/ # Model factories for testing
├── migrations/ # Database migrations
└── seeders/ # Database seeders

## Scheduled Tasks

The application includes several scheduled tasks:

1. Recurring Transaction Reminders:
```
php artisan notifications:cleanup --days=30
```
Sends notifications for upcoming recurring transactions.

2. Notification Cleanup:
```
php artisan notifications:cleanup --days=30
```
Cleans up old read notifications.

## Notifications

The system supports multiple notification types:

1. Budget Exceeded
- Triggered when a transaction causes a budget to be exceeded
- Configurable email and database notifications

2. Savings Goal Milestones
- Triggered when reaching configured milestone percentages
- Default milestones at 25%, 50%, 75%, and 100%

3. Recurring Transaction Reminders
- Daily checks for upcoming recurring transactions
- Notifications sent 24 hours before due date

## Security

- JWT-based authentication
- Route protection via middleware
- Policy-based authorization
- Request validation
- CORS protection

## Error Handling

The API returns standardized error responses:

- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Development

### Setting Up Development Environment

1. Install dependencies:
```bash
composer install
```

2. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

3. Run migrations:
```bash
php artisan migrate
```

4. Seed the database (optional):
```bash
php artisan db:seed
```

### Running Tests

1. Configure testing environment:
```bash
cp .env.testing.example .env.testing
```

2. Run tests:
```bash
php artisan test
```

3. Run with coverage:
```bash
php artisan test --coverage
```

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## Support

For support, please open an issue in the GitHub repository.

## Acknowledgments

- Laravel Framework
- JWT Auth Package
- OpenAPI Initiative