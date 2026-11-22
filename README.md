# COLORS Lab

The COLORS application uses apache2, mysql, and php to host a site that allows users to log in and use a database to add and search for colors.

### Setup
- Requires a LAMP stack droplet hosted on DigitalOcean, or local hosting through Docker or XAMPP
- Clone the repository on the server and configure Apache's `DocumentRoot` to point to the repository's `src/` directory. Deny HTTP access to `src/sql/` and run those scripts directly against MySQL.
- A domain can be setup using the droplet's public IP, otherwise, the site is accessible via http://YourDropletIP

### Codebase Structure
```text
.
├── src/
│   ├── api/               # PHP endpoints and shared helpers
│   ├── css/               # Stylesheets
│   ├── js/                # Browser JavaScript
│   ├── sql/               # Database schema and user setup scripts
│   ├── color.html         # Color dashboard
│   └── index.html         # Login page
├── test/
│   ├── js/                # Jest tests for the browser code
│   └── php/               # PHPUnit tests for PHP API helpers and login responses
└── README.md              # Project documentation
```

### JavaScript linting

The app runs on Apache, MySQL, and PHP. Node.js is used to lint and test
the JavaScript during development.

With Node.js 24 installed, run:

```sh
npm ci
npm run lint
```

GitHub Actions runs ESLint on pushes, pull requests, and manual workflow
runs. The configuration uses ESLint's recommended rules and browser globals
for JavaScript files. Functions called from HTML are marked with an
`exported` comment so unused-variable checks remain enabled.

### Running tests

Run the browser tests with Node.js 24 and Jest.

```sh
npm ci
npm test
npm run test:coverage
```

Jest prints the coverage summary and writes the HTML report to
`coverage/lcov-report/index.html`.

Run the PHP API tests with PHP CLI 8.3 or later, Composer, and the
extensions required by PHPUnit.

```sh
composer install
composer test
```

Neither suite requires MySQL or a running web server.

### Database Structure
The project uses MySQL with database `COP4331` (character set `utf8mb4`, collation `utf8mb4_unicode_ci`).

#### Tables

- **`Users`** — User account records and authentication credentials.
  | Column | Type | Constraints | Description |
  | --- | --- | --- | --- |
  | `ID` | `INT` | `PRIMARY KEY`, `AUTO_INCREMENT` | Unique identifier for each user |
  | `FirstName` | `VARCHAR(50)` | `NOT NULL`, `DEFAULT ''` | User's first name |
  | `LastName` | `VARCHAR(50)` | `NOT NULL`, `DEFAULT ''` | User's last name |
  | `Login` | `VARCHAR(50)` | `NOT NULL`, `UNIQUE` | Unique username / login handle |
  | `Password` | `VARCHAR(255)` | `NOT NULL` | Hashed password |
  | `DateCreated` | `DATETIME` | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP` | Account creation timestamp |

- **`Colors`** — Color entries saved by users.
  | Column | Type | Constraints | Description |
  | --- | --- | --- | --- |
  | `ID` | `INT` | `PRIMARY KEY`, `AUTO_INCREMENT` | Unique identifier for each color entry |
  | `UserID` | `INT` | `NOT NULL`, `INDEX`, `FOREIGN KEY (Users.ID) ON DELETE CASCADE` | Associated user identifier |
  | `Name` | `VARCHAR(50)` | `NOT NULL`, `DEFAULT ''` | Name of the color |
  | `DateCreated` | `DATETIME` | `NOT NULL`, `DEFAULT CURRENT_TIMESTAMP` | Timestamp when color was added |

- **`Sessions`** — Active user authentication sessions.
  | Column | Type | Constraints | Description |
  | --- | --- | --- | --- |
  | `Token` | `CHAR(64)` | `PRIMARY KEY` | 64-character hexadecimal session token |
  | `UserID` | `INT` | `NOT NULL`, `INDEX`, `FOREIGN KEY (Users.ID) ON DELETE CASCADE` | User owning the session |
  | `ExpiresAt` | `DATETIME` | `NOT NULL` | Session expiration timestamp |

### AI Disclosure
- Gemini helped write backend API logic and SQL schemas
