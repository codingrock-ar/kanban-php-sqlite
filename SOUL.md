# Kanban System Soul - Best Practices 

This document outlines the core principles and architectural decisions used in this project to maintain a clean, scalable, and premium codebase.

## 🛠️ Backend Architecture (PHP)
- **Controller-Model Pattern**: Logic is separated between HTTP handling (`Controllers`) and data persistence (`Models`).
- **Autoloading**: No more manual `require` calls. Use the custom autoloader for PSR-4-like organization.
- **SQLite Persistence**: Portable and zero-configuration. Always ensure directory permissions (`777`) for journal files.
- **RESTful-ish API**: Clean routing via `.htaccess` to `api.php`.

## 🎨 Frontend Design System (Vanilla)
- **Design Tokens**: Centralized CSS variables in `:root` for colors, spacing, and shadows.
- **Premium Aesthetics**: 
    - Use `Inter` for typography.
    - Dark mode compatible colors.
    - Subtle transitions (`all 0.2s ease`).
    - Shadow systems for elevation.
- **Modular Assets**: JS and CSS live in `src/Frontend/` for better maintainability.

## 📦 Deployment & Environment
- **Docker First**: Always provide a `Dockerfile` and `docker-compose.yml`.
- **Stateless-ish**: Keep configuration in environment variables when possible.
- **Git Hygiene**: Clean `.gitignore` and descriptive commit messages using Angular/Conventional Commits.

## 🚀 Future Scalability
- **Security**: Implement CSRF tokens and proper session management.
- **Frontend**: Transition to React/Vue if the UI complexity grows.
- **Testing**: Add PHPUnit for backend and Cypress for E2E.

---
*Maintained by the KanbanFlow Team*
