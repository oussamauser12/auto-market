-- 1. Création de la base de données
CREATE DATABASE IF NOT EXISTS auto_market_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE auto_market_db;

-- 2. Table des Marques
CREATE TABLE marques (
    id_marque INT AUTO_INCREMENT PRIMARY KEY,
    nom_marque VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 3. Table des Modèles
CREATE TABLE modeles (
    id_modele INT AUTO_INCREMENT PRIMARY KEY,
    nom_modele VARCHAR(50) NOT NULL,
    id_marque INT NOT NULL,
    CONSTRAINT fk_marque FOREIGN KEY (id_marque) REFERENCES marques(id_marque) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Table des Utilisateurs
CREATE TABLE utilisateurs (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    ville VARCHAR(50),
    role INT DEFAULT 0,  -- 0 = Client, 1 = Admin
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Table des Véhicules
CREATE TABLE vehicules (
    id_vehicule INT AUTO_INCREMENT PRIMARY KEY,
    id_modele INT NOT NULL,
    kilometrage INT NOT NULL,
    annee INT NOT NULL,
    carburant ENUM('Diesel', 'Essence', 'Hybride', 'Electrique') NOT NULL,
    boite_vitesse ENUM('Manuelle', 'Automatique') NOT NULL,
    puissance_fiscale INT DEFAULT NULL,
    CONSTRAINT fk_modele FOREIGN KEY (id_modele) REFERENCES modeles(id_modele)
) ENGINE=InnoDB;

-- 6. Table des Annonces (FIX: ajout de 'refuse' dans l'ENUM)
CREATE TABLE annonces (
    id_annonce INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_vehicule INT NOT NULL,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    prix DECIMAL(12, 2) NOT NULL,
    statut ENUM('en_attente', 'valide', 'vendu', 'archive', 'refuse') DEFAULT 'en_attente',
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_vehicule FOREIGN KEY (id_vehicule) REFERENCES vehicules(id_vehicule) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Table des Photos
CREATE TABLE photos (
    id_photo INT AUTO_INCREMENT PRIMARY KEY,
    id_annonce INT NOT NULL,
    chemin_url VARCHAR(255) NOT NULL,
    CONSTRAINT fk_annonce FOREIGN KEY (id_annonce) REFERENCES annonces(id_annonce) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. Table des Favoris
CREATE TABLE favoris (
    id_favori INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_annonce INT NOT NULL,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fav_user FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_fav_annonce FOREIGN KEY (id_annonce) REFERENCES annonces(id_annonce) ON DELETE CASCADE,
    UNIQUE KEY unique_favori (id_user, id_annonce)
) ENGINE=InnoDB;

-- =============================================
-- DONNÉES DE TEST
-- =============================================

-- Marques et modèles
INSERT INTO marques (nom_marque) VALUES ('Renault'), ('Dacia'), ('Toyota'), ('Peugeot'), ('Volkswagen');
INSERT INTO modeles (nom_modele, id_marque) VALUES
    ('Clio', 1), ('Megane', 1), ('Kangoo', 1),
    ('Logan', 2), ('Sandero', 2), ('Duster', 2),
    ('Yaris', 3), ('Corolla', 3),
    ('208', 4), ('308', 4),
    ('Golf', 5), ('Polo', 5);

-- Utilisateur client (password = '123456' hashé avec bcrypt)
INSERT INTO utilisateurs (nom, prenom, email, password, telephone, ville, role)
VALUES ('Boujadi', 'Oussama', 'client@auto-market.ma',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        '0600000000', 'Khouribga', 0);

-- Utilisateur admin (password = 'admin123' hashé avec bcrypt)
-- Hash de 'admin123': $2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy
INSERT INTO utilisateurs (nom, prenom, email, password, telephone, ville, role)
VALUES ('Admin', 'Auto-Market', 'admin@auto-market.ma',
        '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
        '0600000001', 'Casablanca', 1);

-- Véhicules et annonces de démo
INSERT INTO vehicules (id_modele, kilometrage, annee, carburant, boite_vitesse, puissance_fiscale)
VALUES (1, 120000, 2018, 'Diesel', 'Manuelle', 6);
INSERT INTO annonces (id_user, id_vehicule, titre, description, prix, statut)
VALUES (1, 1, 'Renault Clio 4 - Très bon état', 'Vends Clio 4 en excellent état, bien entretenue, full options.', 95000, 'valide');

INSERT INTO vehicules (id_modele, kilometrage, annee, carburant, boite_vitesse, puissance_fiscale)
VALUES (4, 85000, 2020, 'Diesel', 'Manuelle', 5);
INSERT INTO annonces (id_user, id_vehicule, titre, description, prix, statut)
VALUES (1, 2, 'Dacia Logan - État Neuf', 'Une excellente voiture économique, 1er propriétaire.', 105000, 'valide');

INSERT INTO vehicules (id_modele, kilometrage, annee, carburant, boite_vitesse, puissance_fiscale)
VALUES (6, 45000, 2021, 'Diesel', 'Automatique', 8);
INSERT INTO annonces (id_user, id_vehicule, titre, description, prix, statut)
VALUES (1, 3, 'Dacia Duster 4x4 - Automatique', 'Duster en parfait état, boite auto, climatisation, GPS.', 195000, 'valide');
