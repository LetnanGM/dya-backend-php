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
CREATE TABLE IF NOT EXISTS members (
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

CREATE TABLE IF NOT EXISTS member_interests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    interest VARCHAR(100) NOT NULL,

    FOREIGN KEY (member_id)
        REFERENCES members(id)
        ON DELETE CASCADE,

    UNIQUE (member_id, interest)
);

CREATE TABLE IF NOT EXISTS member_gallery (
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

CREATE TABLE IF NOT EXISTS member_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id BIGINT UNSIGNED NOT NULL,
    platform VARCHAR(50) NOT NULL,
    url VARCHAR(500) NOT NULL,

    FOREIGN KEY (member_id)
        REFERENCES members(id)
        ON DELETE CASCADE,

    UNIQUE (member_id, platform)
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
('Rais Dirja Rahmadi', 'images/cp-admin-ijj.webp', TRUE, 'Cyber Security & Founder of IJJ', 'Theres no System is Safe. CyberSec from Indonesia.', '#', '#', '#');

INSERT IGNORE INTO shortlinks (shortlink, longlink) VALUES
('dya', 'https://www.dya.web.id'),
('github', 'https://github.com/LetnanGM');

INSERT IGNORE INTO users (username, password, email) VALUES
('admin', '$2b$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQ8Zf0sY6jI5Fh5g5a5eG', 'admin@example.com');

/* seed members (dipindahkan dari src/data/member.php; aman diimpor berulang) */
INSERT IGNORE INTO members (name, username, bio, about, avatar, cover, location, joined_at) VALUES
('Arya', 'arya', 'Building things, exploring technology, and learning something new every day.', 'Hello! I enjoy exploring technology, understanding how things work, and building projects. I believe that learning is a continuous journey.', 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=300&h=300&fit=crop', 'https://images.unsplash.com/photo-1519608487953-e999c86e7455?w=1600&h=600&fit=crop', 'Indonesia', '2025-01-12'),
('Faiz', 'faiz', 'Enjoying life, making memories, and being part of something.', 'Just enjoying the journey, connecting with people, and collecting memorable experiences.', NULL, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?w=1600&h=600&fit=crop', 'Indonesia', '2026-01-10'),
('Raihan', 'raihan', 'A curious mind with a passion for creativity.', 'Interested in creative things, visual inspiration, and discovering new perspectives.', NULL, NULL, 'Indonesia', '2026-02-15'),
('Dimas', 'dimas', 'Just enjoying the journey and meeting new people.', 'Nothing special, just another person with stories to tell.', NULL, NULL, NULL, '2026-03-01');

/* interests: UNIQUE (member_id, interest) membuat INSERT IGNORE aman diulang */
INSERT IGNORE INTO member_interests (member_id, interest)
SELECT m.id, i.interest FROM members m
CROSS JOIN (
    SELECT 'Technology' AS interest UNION ALL SELECT 'Backend' UNION ALL SELECT 'Linux' UNION ALL SELECT 'Open Source'
) i WHERE m.username = 'arya';

INSERT IGNORE INTO member_interests (member_id, interest)
SELECT m.id, i.interest FROM members m
CROSS JOIN (
    SELECT 'Gaming' AS interest UNION ALL SELECT 'Music' UNION ALL SELECT 'Photography'
) i WHERE m.username = 'faiz';

INSERT IGNORE INTO member_interests (member_id, interest)
SELECT m.id, i.interest FROM members m
CROSS JOIN (
    SELECT 'Design' AS interest UNION ALL SELECT 'Art' UNION ALL SELECT 'Photography'
) i WHERE m.username = 'raihan';

INSERT IGNORE INTO member_interests (member_id, interest)
SELECT m.id, 'Gaming' FROM members m WHERE m.username = 'dimas';

/* links & gallery: hanya di-seed jika member tersebut belum punya data */
INSERT INTO member_links (member_id, platform, url)
SELECT m.id, 'GitHub', 'https://github.com/LetnanGM' FROM members m
WHERE m.username = 'arya'
  AND NOT EXISTS (SELECT 1 FROM member_links l WHERE l.member_id = m.id);

INSERT INTO member_gallery (member_id, image, thumbnail, caption, uploaded_at, visibility)
SELECT m.id, g.image, g.thumbnail, g.caption, g.uploaded_at, 'public' FROM members m
CROSS JOIN (
    SELECT 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200' AS image, 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600' AS thumbnail, 'A little workspace inspiration.' AS caption, '2026-10-10' AS uploaded_at
    UNION ALL SELECT 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200', 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=600', 'Exploring technology.', '2026-10-15'
    UNION ALL SELECT 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1200', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600', 'Learning and experimenting.', '2026-10-20'
    UNION ALL SELECT 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?w=1200', 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?w=600', 'Somewhere worth remembering.', '2026-10-25'
) g
WHERE m.username = 'arya'
  AND NOT EXISTS (SELECT 1 FROM member_gallery x WHERE x.member_id = m.id);

INSERT INTO member_gallery (member_id, image, thumbnail, caption, uploaded_at, visibility)
SELECT m.id, g.image, g.thumbnail, g.caption, g.uploaded_at, 'public' FROM members m
CROSS JOIN (
    SELECT 'https://images.unsplash.com/photo-1470252649378-9c29740c9fa8?w=1200' AS image, 'https://images.unsplash.com/photo-1470252649378-9c29740c9fa8?w=600' AS thumbnail, 'A beautiful morning.' AS caption, '2026-09-10' AS uploaded_at
    UNION ALL SELECT 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=1200', 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=600', 'Nature never disappoints.', '2026-09-15'
) g
WHERE m.username = 'faiz'
  AND NOT EXISTS (SELECT 1 FROM member_gallery x WHERE x.member_id = m.id);
