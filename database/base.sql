CREATE DATABASE IF NOT EXISTS dya_db;
USE dya_db;

CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thumb_image VARCHAR(255),
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    techstack TEXT NOT NULL,
    linkview VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL DEFAULT 'DyA Teams',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    profil_img VARCHAR(255) NOT NULL,
    status BOOLEAN NOT NULL DEFAULT TRUE,
    peran VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    linkgithub VARCHAR(255) UNIQUE NOT NULL,
    linklinkedin VARCHAR(255) UNIQUE NOT NULL,
    linkinstagram VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB; -- FIXED: Added missing semicolon here

CREATE TABLE IF NOT EXISTS shortlinks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shortlink VARCHAR(255) UNIQUE NOT NULL,
    longlink VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

/* CREATE A USER TABLE :V */

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

/* CREATE MEMBERRRRR TABLEEEEEE */
CREATE TABLE members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    bio VARCHAR(255),
    about TEXT,
    avatar VARCHAR(500),
    cover VARCHAR(500),
    location VARCHAR(100),
    joined_at DATE NOT NULL DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE member_interests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    interest VARCHAR(100) NOT NULL,

    FOREIGN KEY (member_id)
        REFERENCES members(id)
        ON DELETE CASCADE,

    UNIQUE (member_id, interest)
);

CREATE TABLE member_gallery (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    image VARCHAR(500) NOT NULL,
    thumbnail VARCHAR(500),
    caption VARCHAR(255),
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    visibility ENUM('public', 'private') DEFAULT 'public',

    FOREIGN KEY (member_id)
        REFERENCES members(id)
        ON DELETE CASCADE
);

CREATE TABLE member_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    platform VARCHAR(50) NOT NULL,
    url VARCHAR(500) NOT NULL,

    FOREIGN KEY (member_id)
        REFERENCES members(id)
        ON DELETE CASCADE
);

/* insert default data */
INSERT IGNORE INTO projects (thumb_image, name, description, techstack, linkview, author) VALUES
('https://placehold.co/600x360?text=E-Commerce+API', 'E-Commerce API REST', 'A robust, scalable backend built with Node.js, Express, and PostgreSQL featuring JWT authentication and Stripe integration.', '["Node.js", "PostgreSQL"]', '#HEREISLINK', 'DyA Teams'),
('https://placehold.co/600x360?text=Task+Service', 'Task Management Service', 'A microservice architecture application running on Docker, utilizing Redis queues for processing background email notifications.', '["Python", "Redis"]', '#HEREISLINK', 'DyA Teams'), -- FIXED: Changed semicolon to a comma
('https://placehold.co/600x360?text=Portfolio+Website', 'Portfolio Website', 'A personal portfolio website showcasing projects and skills, built with React and hosted on Netlify.', '["React", "Netlify"]', '#HEREISLINK', 'DyA Teams'),
('https://placehold.co/600x360?text=Blog+Platform', 'Blog Platform', 'A full-featured blogging platform with user authentication, content management, and SEO optimization.', '["Django", "PostgreSQL"]', '#HEREISLINK', 'DyA Teams'),
('https://placehold.co/600x360?text=Chat+Application', 'Real-time Chat Application', 'A real-time chat application using WebSockets for instant messaging and notifications.', '["Node.js", "Socket.io"]', '#HEREISLINK', 'DyA Teams'),
('https://placehold.co/600x360?text=Weather+App', 'Weather Forecast Application', 'A weather forecast application that fetches data from a public API and displays it in a user-friendly interface.', '["JavaScript", "API"]', '#HEREISLINK', 'DyA Teams');

INSERT IGNORE INTO teams (name, profil_img, status, peran, description, linkgithub, linklinkedin, linkinstagram) VALUES
('Muhammad Faiz Al-khalifi', 'images/dev-2.webp', TRUE, 'Junior WebDev & Beginner Dev', 'Fokus pada dampak sosial dan kolaborasi', '#', '#', '#'),
('Syafiq Aryasofa Pangestu', 'images/dev-1.webp', TRUE, 'Founder of DyA & Backend Dev', 'junior high student from Indonesia 🇮🇩, building backend things one commit at a time.', '#', '#', '#'),
('Rais Dirja Rahmadai', 'images/cp-admin-ijj.webp', TRUE, 'Cyber Security & Founder of IJJ', 'Theres no System is Safe. CyberSec from Indonesia.', '#', '#', '#');

INSERT IGNORE INTO shortlinks (shortlink, longlink) VALUES
('dya', 'https://www.dya.web.id'),
('github', 'https://github.com/LetnanGM');

INSERT IGNORE INTO users (username, password, email) VALUES
('admin', '$2b$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQ8Zf0sY6jI5Fh5g5a5eG', 'admin@example.com');