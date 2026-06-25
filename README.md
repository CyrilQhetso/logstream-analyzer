# LogStream - Universal Log Analyzer

LogStream is a lightweight web application built with Laravel 11 that allows users to upload or paste log files and instantly visualize log severity levels.

The application supports multiple log formats, automatically detects log types, parses entries, and generates interactive charts to help developers quickly understand what's happening inside their systems.

---

## Features

### Log Upload

* Upload log files directly from your computer
* Supports:

  * `.log`
  * `.txt`
  * `.out`
  * `.err`
* Maximum file size: 20 MB

### Paste Logs Directly

* Paste terminal output or log snippets
* Instant analysis without creating a file

### Multi-Format Log Detection

Supported formats include:

* Laravel / Monolog
* Apache Error Logs
* Apache Access Logs
* Nginx Logs
* Python Logging / Django
* Node.js / Winston
* Node.js JSON Logs
* Syslog
* Ruby on Rails Logs
* Docker Logs
* Generic Severity-Based Logs

### Severity Analysis

Automatically detects and categorizes:

* EMERGENCY
* ALERT
* CRITICAL
* ERROR
* WARNING
* NOTICE
* INFO
* DEBUG

### Interactive Dashboard

* Severity breakdown charts
* Log statistics
* Match rate calculation
* Format detection
* Filterable log stream
* Dark/Light mode

### User Experience Features

* Drag and drop uploads
* Keyboard-friendly navigation
* Analyze another log button
* Large-line protection
* Responsive design

---

## Tech Stack

### Backend

* Laravel 11
* PHP 8.2+

### Frontend

* Blade Templates
* Tailwind CSS (CDN)
* Chart.js

### Storage

* Session-based storage
* No database required

---

## Why No Database?

LogStream is intentionally designed as a lightweight monolithic application.

Benefits:

* Simple setup
* Fast deployment
* No migrations
* No database maintenance
* Easy to run locally or in the cloud

---

## Installation

### Clone Repository

```bash
git clone https://github.com/CyrilQhetso/logstream-analyzer.git
cd logstream-analyzer
```

### Install Dependencies

```bash
composer install
```

### Create Environment File

```bash
cp .env.example .env
```

### Generate Application Key

```bash
php artisan key:generate
```

### Start Development Server

```bash
php artisan serve
```

Visit:

```text
http://localhost:8000
```

---

## Project Structure

```text
app/
 └── Http/
     └── Controllers/
         └── LogController.php

resources/
 └── views/
     └── dashboard.blade.php

routes/
 └── web.php
```

The application intentionally keeps the architecture simple:

* One Controller
* One Blade View
* No Database
* No Authentication

---

## Example Log Formats

### Laravel

```text
[2026-06-20 08:00:01] production.ERROR: Database connection failed
```

### Apache Error Log

```text
[Wed Jun 20 08:05:00.123456 2026] [error] Failed to connect to backend service
```

### Python

```text
2026-06-20 08:15:20,789 ERROR auth.database Database query failed
```

### Node.js

```text
2026-06-20T08:20:20.000Z [ERROR]: Unhandled promise rejection
```

### JSON Logs

```json
{
  "level": "error",
  "message": "Failed to process payment",
  "timestamp": "2026-06-20T08:25:20.000Z"
}
```

## Screenshots

### Upload Interface
<img width="1913" height="958" alt="Screenshot 2026-06-25 110725" src="https://github.com/user-attachments/assets/76d26c26-1329-4e32-945a-fe0358892efd" />


### Analysis Dashboard
<img width="1917" height="965" alt="Screenshot 2026-06-25 110759" src="https://github.com/user-attachments/assets/ba07e045-056f-4610-94d1-3fc1856a7dcc" />
<img width="1913" height="967" alt="Screenshot 2026-06-25 110814" src="https://github.com/user-attachments/assets/ccb2565f-5a8b-4061-94da-3b731fe6ef70" />



---

## Deployment

This project can be deployed on:

* Render

## Author

Developed by Tsoelopele Cyril Qhetso

Software Engineer | Full Stack Developer

live demo:
https://logstream-analyzer.onrender.com/
