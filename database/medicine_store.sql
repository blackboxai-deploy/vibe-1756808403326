-- Medicine Store Database Schema
-- Drop existing database if exists and create new one
DROP DATABASE IF EXISTS medicine_store;
CREATE DATABASE medicine_store;
USE medicine_store;

-- Users table for customer accounts
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table for medicine categories
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Medicines table for product catalog
CREATE TABLE medicines (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    stock_quantity INT DEFAULT 0,
    image VARCHAR(255),
    prescription_required BOOLEAN DEFAULT FALSE,
    manufacturer VARCHAR(100),
    expiry_date DATE,
    dosage VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Admin users table
CREATE TABLE admin_users (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table for shopping cart items
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id) ON DELETE CASCADE
);

-- Orders table for order records
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order items table for individual items in orders
CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id)
);

-- Insert sample categories
INSERT INTO categories (category_name, description) VALUES
('Pain Relief', 'Medications for pain management and relief'),
('Antibiotics', 'Prescription antibiotics for bacterial infections'),
('Vitamins & Supplements', 'Health supplements and vitamin products'),
('Cold & Flu', 'Medications for cold and flu symptoms'),
('Digestive Health', 'Medications for digestive and stomach issues'),
('Heart & Blood Pressure', 'Cardiovascular medications'),
('Diabetes Care', 'Medications and supplies for diabetes management'),
('Skin Care', 'Topical medications and skin treatments');

-- Insert sample medicines
INSERT INTO medicines (name, description, price, category_id, stock_quantity, image, prescription_required, manufacturer, expiry_date, dosage) VALUES
('Paracetamol 500mg', 'Effective pain reliever and fever reducer', 12.50, 1, 100, 'https://placehold.co/300x300?text=Paracetamol+500mg+tablets+white+medicine+box', FALSE, 'PharmaCorp', '2025-12-31', '500mg'),
('Ibuprofen 400mg', 'Anti-inflammatory pain relief medication', 18.75, 1, 80, 'https://placehold.co/300x300?text=Ibuprofen+400mg+anti+inflammatory+medicine+orange+box', FALSE, 'MediHealth', '2025-08-15', '400mg'),
('Amoxicillin 250mg', 'Broad spectrum antibiotic for bacterial infections', 45.00, 2, 50, 'https://placehold.co/300x300?text=Amoxicillin+250mg+antibiotic+capsules+blue+box', TRUE, 'BioMed Labs', '2025-06-30', '250mg'),
('Vitamin C 1000mg', 'Immune system support vitamin supplement', 25.30, 3, 120, 'https://placehold.co/300x300?text=Vitamin+C+1000mg+orange+tablets+health+supplement', FALSE, 'VitaLife', '2026-03-20', '1000mg'),
('Cough Syrup', 'Effective relief for dry and wet cough', 22.90, 4, 60, 'https://placehold.co/300x300?text=Cough+Syrup+bottle+purple+medicine+cold+flu', FALSE, 'PharmaCorp', '2025-10-12', '10ml'),
('Omeprazole 20mg', 'Proton pump inhibitor for acid reflux', 35.60, 5, 40, 'https://placehold.co/300x300?text=Omeprazole+20mg+acid+reflux+medicine+green+box', TRUE, 'GastroMed', '2025-09-25', '20mg'),
('Aspirin 75mg', 'Low dose aspirin for heart health', 15.25, 6, 90, 'https://placehold.co/300x300?text=Aspirin+75mg+heart+health+small+white+tablets', FALSE, 'CardioHealth', '2025-11-18', '75mg'),
('Metformin 500mg', 'Diabetes medication for blood sugar control', 28.80, 7, 70, 'https://placehold.co/300x300?text=Metformin+500mg+diabetes+medication+blue+white+box', TRUE, 'DiabetoCare', '2025-07-08', '500mg'),
('Hydrocortisone Cream', 'Topical anti-inflammatory for skin conditions', 19.45, 8, 85, 'https://placehold.co/300x300?text=Hydrocortisone+Cream+skin+treatment+tube+medical', FALSE, 'DermaCare', '2025-12-01', 'Topical'),
('Cetirizine 10mg', 'Antihistamine for allergy relief', 16.70, 4, 95, 'https://placehold.co/300x300?text=Cetirizine+10mg+allergy+relief+tablets+yellow+box', FALSE, 'AllergyFree', '2025-08-30', '10mg');

-- Insert admin user (password: admin123)
INSERT INTO admin_users (username, email, password, full_name) VALUES
('admin', 'admin@medicinestore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- Insert sample user (password: user123)
INSERT INTO users (username, email, password, full_name, phone, address) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '123-456-7890', '123 Main St, City, State 12345');