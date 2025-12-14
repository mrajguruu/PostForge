-- =============================================
-- Blog Management System - Database Schema
-- =============================================

-- Note: Database already created via hosting control panel
-- USE statement removed for hosting compatibility

-- =============================================
-- Table: admins
-- Stores admin user information
-- =============================================
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: categories
-- Stores blog post categories
-- =============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: posts
-- Stores blog posts
-- =============================================
CREATE TABLE IF NOT EXISTS posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    category_id INT,
    author_id INT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_author (author_id),
    INDEX idx_created (created_at),
    FULLTEXT idx_search (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: comments
-- Stores post comments
-- =============================================
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    INDEX idx_post (post_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Insert Demo Data
-- =============================================

-- Insert demo admin user
-- Password: admin123 (hashed with bcrypt)
INSERT INTO admins (username, email, password, full_name) VALUES
('admin', 'admin@blog.com', '$2y$10$f.s0Tb9f5oDpJ9HGAN8AX.chQn46O/cbFqCtJfwFAzLR9YYc6WGEe', 'Admin User');

-- Insert demo categories
INSERT INTO categories (name, slug, description) VALUES
('Technology', 'technology', 'All about technology, programming, and software development'),
('Travel', 'travel', 'Travel guides, tips, and destination reviews'),
('Food & Recipe', 'food-recipe', 'Delicious recipes and cooking tips'),
('Lifestyle', 'lifestyle', 'Lifestyle, health, and wellness articles'),
('Business', 'business', 'Business insights, entrepreneurship, and career advice');

-- Insert demo posts
INSERT INTO posts (title, slug, content, excerpt, featured_image, category_id, author_id, status, views, published_at) VALUES
(
    'Getting Started with Web Development',
    'getting-started-with-web-development',
    '<h2>Introduction to Web Development</h2><p>Web development is an exciting field that combines creativity with technical skills. In this comprehensive guide, we''ll explore the fundamental technologies you need to know to become a successful web developer.</p><h3>HTML: The Foundation</h3><p>HTML (HyperText Markup Language) is the backbone of every website. It provides structure and meaning to web content through various tags and elements.</p><h3>CSS: Making it Beautiful</h3><p>CSS (Cascading Style Sheets) transforms plain HTML into visually appealing designs. Learn about layouts, colors, typography, and responsive design.</p><h3>JavaScript: Adding Interactivity</h3><p>JavaScript brings websites to life with dynamic behavior and interactive features. Master the fundamentals and explore modern frameworks.</p>',
    'Learn the essential technologies and skills needed to start your journey in web development.',
    'webdevelopment.webp',
    1,
    1,
    'published',
    1234,
    NOW()
),
(
    '10 Must-Visit Destinations in Europe',
    '10-must-visit-destinations-europe',
    '<h2>Explore the Best of Europe</h2><p>Europe offers incredible diversity in culture, history, and natural beauty. Here are ten destinations that should be on every traveler''s bucket list.</p><h3>1. Paris, France</h3><p>The City of Light captivates with its iconic landmarks, world-class museums, and romantic atmosphere.</p><h3>2. Rome, Italy</h3><p>Step back in time and explore ancient ruins, Renaissance art, and incredible Italian cuisine.</p><h3>3. Barcelona, Spain</h3><p>Discover Gaudí''s architectural masterpieces and enjoy the vibrant Mediterranean culture.</p>',
    'Discover the most beautiful and culturally rich destinations across Europe.',
    'europe-travel-demo.webp',
    2,
    1,
    'published',
    856,
    NOW()
),
(
    'Healthy Breakfast Recipes',
    'healthy-breakfast-recipes',
    '<h2>Start Your Day Right</h2><p>A nutritious breakfast sets the tone for the entire day. These recipes are not only healthy but also delicious and easy to prepare.</p><h3>Overnight Oats</h3><p>Combine oats, milk, chia seeds, and your favorite fruits in a jar. Refrigerate overnight and enjoy a ready-made breakfast.</p><h3>Avocado Toast</h3><p>Mash ripe avocado on whole grain toast, add a poached egg, and season with salt, pepper, and red pepper flakes.</p>',
    'Quick and nutritious breakfast ideas to energize your mornings.',
    'breakfast-recipe-demo.webp',
    3,
    1,
    'published',
    542,
    NOW()
),
(
    'Work-Life Balance in the Modern World',
    'work-life-balance-modern-world',
    '<h2>Finding Balance</h2><p>In today''s fast-paced world, maintaining a healthy work-life balance is more important than ever. Here''s how to achieve it.</p><h3>Set Boundaries</h3><p>Learn to separate work time from personal time. Turn off notifications after hours and create a dedicated workspace.</p><h3>Prioritize Self-Care</h3><p>Regular exercise, adequate sleep, and mindfulness practices are essential for mental and physical well-being.</p>',
    'Strategies for maintaining balance between professional and personal life.',
    'work-life-balance-demo.webp',
    4,
    1,
    'draft',
    0,
    NULL
);

-- Insert demo comments
INSERT INTO comments (post_id, author_name, author_email, content, status) VALUES
(1, 'John Doe', 'john@example.com', 'Great article! Very helpful for beginners. I especially liked the section on JavaScript frameworks.', 'approved'),
(1, 'Jane Smith', 'jane@example.com', 'Thanks for sharing this comprehensive guide. Could you recommend some resources for learning CSS Grid?', 'approved'),
(2, 'Mike Johnson', 'mike@example.com', 'I visited Paris last year and it was amazing! Your description captures it perfectly.', 'approved'),
(3, 'Sarah Williams', 'sarah@example.com', 'I tried the overnight oats recipe and it''s delicious! Adding some honey made it even better.', 'pending');

-- =============================================
-- End of Schema
-- =============================================
