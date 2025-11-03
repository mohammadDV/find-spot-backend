# Find-Spot-backend

Find Spot This platform helps users discover local businesses, share experiences, and read authentic reviews.
It provides ratings, photos, and detailed insights to guide better choices.
Users can easily search by location, category, or service type.
The goal is to connect people with trusted places around them.

## Table of Contents

-   [Features](#features)
-   [Admin Panel with Filament](#admin-panel-with-filament)
-   [Business Structure & DDD](#business-structure--ddd)
-   [How to Run](#how-to-run)
-   [Contributing](#contributing)
-   [License](#license)

---

## Features

-   Discover and explore local businesses by location, category, or service type.
-   Read and write authentic reviews with photos and ratings.
-   Favorite businesses and save them for future reference.
-   Real-time chat and messaging system between users.
-   Event management for businesses.
-   Powerful admin panel built with **Filament PHP**.
-   Modular, scalable backend using Domain-Driven Design (DDD).

---

## Admin Panel with Filament

This project includes a comprehensive admin panel built with **[Filament PHP](https://filamentphp.com/)**, a modern TALL stack (Tailwind, Alpine, Laravel, Livewire) admin panel framework.

### What is Filament?

Filament is a powerful admin panel and form builder for Laravel applications. It provides a beautiful, intuitive interface for managing your application's data with minimal code.

### Admin Panel Features

-   **Resource Management**: Full CRUD operations for all domain entities (Businesses, Users, Reviews, Posts, Events, etc.)
-   **Custom Widgets**: Dashboard widgets for analytics and insights
-   **Role-Based Access Control**: Integrated with Spatie Laravel Permission for fine-grained access control
-   **Rich Form Fields**: Built-in support for various field types including file uploads, relationships, and rich text
-   **Responsive Design**: Mobile-friendly interface with RTL support for Persian language
-   **Custom Actions**: Bulk operations and custom actions for business workflows

### Accessing the Admin Panel

Once your application is running, you can access the Filament admin panel at:

```
http://localhost:8000/admin
```

To create an admin user, run:

```bash
php artisan make:filament-user
```

### Admin Panel Structure

```
app/Filament/
├── Resources/        # Resource classes for managing models
├── Widgets/          # Dashboard widgets
└── Providers/        # Filament service providers
```

---

## Business Structure & DDD

This business is architected using **Domain-Driven Design (DDD)** principles, which means the codebase is organized around the core business domains and their logic. Here's how the structure is laid out:

```
src/
├── Domain/
│   ├── Review/
│   │   ├── Models/
│   │   ├── Repositories/
│   │   │   ├── ReviewRepository.php
│   │   │   └── Contracts/
│   │   │       └── IReviewRepository.php
│   ├── User/
│   ├── Bussiness/
│   └── ... (other subdomains)
│
├── Application/
│   ├── Api/
│   │   ├── Review/
│   │   ├── User/
│   │   ├── Business/
│   │   └── ... (other APIs)
│   └── Application.php
│
├── Core/
│   ├── Http/
│   ├── Providers/
│   ├── Exceptions/
│   └── Console/
│
└── Support/
```

### DDD Layers Explained

-   **Domain Layer (`src/Domain/`)**:  
    Contains the heart of the business logic. Each subdomain (e.g., Review, User, Business) has its own folder, with:

    -   `Models/`: Eloquent models representing domain entities.
    -   `Repositories/`: Data access logic, often split into `Contracts/` (interfaces) and concrete implementations.

-   **Application Layer (`src/Application/`)**:  
    Coordinates application activities. The `Api/` directory contains controllers, requests, and resources for each subdomain, handling HTTP requests and responses.

-   **Core Layer (`src/Core/`)**:  
    Contains shared infrastructure, such as HTTP handling, service providers, exceptions, and console commands.

-   **Support Layer (`src/Support/`)**:  
    (If used) Contains helpers, utilities, or cross-cutting concerns.

### Example: Review Subdomain

-   `Domain/Review/Models/Review.php`: The Review entity.
-   `Domain/Review/Repositories/Contracts/IReviewRepository.php`: The repository interface for reviews.
-   `Domain/Review/Repositories/ReviewRepository.php`: The concrete implementation of the review repository.
-   `Application/Api/Review/Controllers/ReviewController.php`: Handles HTTP requests for reviews.
-   `Application/Api/Review/Requests/ReviewRequest.php`: Handles validation for review-related requests.

---

## How to Run

1. **Clone the repository:**

    ```bash
    git clone https://github.com/yourusername/find-spot-backend.git
    cd Find Spot-backend
    ```

2. **Install dependencies:**

    ```bash
    composer install
    ```

3. **Set up your environment:**

    - Copy `.env.example` to `.env` and configure your database and other settings.

4. **Run migrations:**

    ```bash
    php artisan migrate
    ```

5. **Start the development server:**

    ```bash
    php artisan serve
    ```

6. **Access the admin panel (optional):**

    Create a Filament admin user:

    ```bash
    php artisan make:filament-user
    ```

    Then visit `http://localhost:8000/admin` to access the Filament admin panel.

---

## Contributing

Contributions are welcome! Please open issues or submit pull requests for any improvements or bug fixes.

---

## License

This business is open-source and available under the [MIT License](LICENSE).
