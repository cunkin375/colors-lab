# COLORS Lab

The COLORS application uses apache2, mysql, and php to host a site that allows users to log in and use a database to add and search for colors.

### Setup
- Requires a LAMP stack droplet hosted on DigitalOcean, or local hosting through Docker or XAMPP
- If using a droplet, `ssh` into the droplet and clone this repository in `/var/www/html/`
- A domain can be setup using the droplet's public IP, otherwise, the site is accessible via http://YourDropletIP

### Codebase Structure
```text
.
├── api/
│   ├── add_color.php      # Endpoint to add a new color for the authenticated user
│   ├── common.php         # Shared utilities (JSON helpers, session handling, validation)
│   ├── config.php         # Database configuration & environment variable fallback
│   ├── db.php             # MySQL database connection helper (mysqli)
│   ├── login.php          # User authentication and session token generation
│   └── search_colors.php  # Endpoint to search user's saved colors
├── css/
│   └── styles.css         # UI styles and typography
├── js/
│   └── code.js            # Client-side logic, API calls, and cookie/session management
├── sql/
│   ├── create_db_user.sql # SQL script to create a restricted database user
│   ├── schema.sql         # Database and table definitions (Users, Colors, Sessions)
│   └── seed.sql           # Seed data for testing and development
├── color.html             # Application dashboard (search and add colors)
├── index.html             # Login landing page
└── README.md              # Project documentation
```

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
