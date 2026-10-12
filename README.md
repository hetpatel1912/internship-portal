# Student Internship Portal

A student internship portal built with HTML, CSS, JavaScript, AngularJS, PHP, and MySQL.

## Deploying on Render

This project uses a Docker image with Apache, PHP 8.3, and the PDO MySQL extension. Render does not automatically provide a MySQL database for this service; create/provision a reachable MySQL database with the required privileges first.

### 1. Create the hosted MySQL database

Create a MySQL database with a database name, host, port, username, and password. Make sure remote connections from Render are allowed by the database provider. Do not commit credentials to GitHub.

### 2. Push this folder to GitHub

Upload the contents of this folder (not the ZIP file itself) to a new GitHub repository. Keep `.env` and all passwords out of Git.

### 3. Create the Render service

In Render, create a **Web Service** from the GitHub repository and choose **Docker** as the runtime. Render can also use the included `render.yaml` blueprint. Set these environment variables in Render:

- `DB_HOST`: database hostname
- `DB_PORT`: database port, usually `3306`
- `DB_NAME`: database name
- `DB_USER`: database username
- `DB_PASSWORD`: database password
- `ADMIN_EMAIL`: administrator email
- `ADMIN_PASSWORD`: strong initial administrator password (at least 12 characters)
- `SETUP_KEY`: a long random one-time setup key

### 4. Initialize tables once

After deployment, open `/setup.php?key=YOUR_SETUP_KEY` on the deployed domain. This creates the tables and sample internship records, and creates the administrator account if it does not already exist. The setup key must be set in Render first. After successful setup, remove `SETUP_KEY` from the Render environment and redeploy. Do not leave setup enabled.

### 5. Verify the site

Test registration, login/logout, internship search, applications, profile updates, and admin functions. Check Render logs if database connection errors occur.

## Local development with XAMPP

1. Put the folder under `htdocs`.
2. Create a MySQL database named `internship_portal` and configure environment variables, or update the local environment to match your MySQL credentials.
3. Run the one-time setup only after setting `SETUP_KEY` and `ADMIN_PASSWORD` in your local PHP environment.
4. Open the project through Apache.

## Important security notes

- Never commit passwords, tokens, or database connection strings.
- Change the administrator email/password before real use.
- Disable the one-time setup endpoint after initialization.
- This project is a college-project starting point; perform a security review before using it with real student data.
