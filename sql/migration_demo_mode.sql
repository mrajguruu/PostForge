-- =============================================
-- Migration: Add Demo Mode Support
-- This script adds is_demo column to existing database
-- Run this on your production database (InfinityFree)
-- =============================================

-- Add is_demo column to categories table
ALTER TABLE categories ADD COLUMN is_demo TINYINT(1) DEFAULT 0 AFTER description;

-- Add is_demo column to posts table
ALTER TABLE posts ADD COLUMN is_demo TINYINT(1) DEFAULT 0 AFTER views;

-- Add is_demo column to comments table
ALTER TABLE comments ADD COLUMN is_demo TINYINT(1) DEFAULT 0 AFTER status;

-- Mark existing data as demo content
UPDATE categories SET is_demo = 1;
UPDATE posts SET is_demo = 1;
UPDATE comments SET is_demo = 1;

-- =============================================
-- Verification Queries (Optional - for testing)
-- =============================================

-- Check categories
-- SELECT id, name, is_demo FROM categories;

-- Check posts
-- SELECT id, title, is_demo FROM posts;

-- Check comments
-- SELECT id, author_name, is_demo FROM comments;
