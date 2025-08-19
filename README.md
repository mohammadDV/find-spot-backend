# Find-Spot-backend

Find Spot This platform helps users discover local businesses, share experiences, and read authentic reviews.
It provides ratings, photos, and detailed insights to guide better choices.
Users can easily search by location, category, or service type.
The goal is to connect people with trusted places around them.

## Table of Contents

-   [Features](#features)
-   [Business Structure & DDD](#business-structure--ddd)
-   [How to Run](#how-to-run)
-   [Contributing](#contributing)
-   [License](#license)

---

## Features

-   Travelers can accept and deliver packages for others.
-   Senders can find travelers going to their desired destination.
-   Secure, efficient, and affordable delivery system.
-   Modular, scalable backend using Domain-Driven Design (DDD).

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

---

## Contributing

Contributions are welcome! Please open issues or submit pull requests for any improvements or bug fixes.

---

## License

This business is open-source and available under the [MIT License](LICENSE).
