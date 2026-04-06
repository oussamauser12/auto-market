-- 1. Création de la base de données
CREATE DATABASE IF NOT EXISTS auto_market_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE auto_market_db;

-- 2. Table des Marques (ex: Renault, Dacia)
CREATE TABLE marques (
    id_marque INT AUTO_INCREMENT PRIMARY KEY,
    nom_marque VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 3. Table des Modèles (ex: Clio, Logan)
CREATE TABLE modeles (
    id_modele INT AUTO_INCREMENT PRIMARY KEY,
    nom_modele VARCHAR(50) NOT NULL,
    id_marque INT NOT NULL,
    CONSTRAINT fk_marque FOREIGN KEY (id_marque) REFERENCES marques(id_marque) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Table des Utilisateurs (Clients et Admins)
CREATE TABLE utilisateurs (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Sera haché en PHP
    telephone VARCHAR(20),
    ville VARCHAR(50),
    role INT DEFAULT 0, -- 0 = Client (Vente/Achat), 1 = Admin
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Table des Véhicules (Détails techniques)
CREATE TABLE vehicules (
    id_vehicule INT AUTO_INCREMENT PRIMARY KEY,
    id_modele INT NOT NULL,
    kilometrage INT NOT NULL,
    annee INT NOT NULL,
    carburant ENUM('Diesel', 'Essence', 'Hybride', 'Electrique') NOT NULL,
    boite_vitesse ENUM('Manuelle', 'Automatique') NOT NULL,
    puissance_fiscale INT,
    CONSTRAINT fk_modele FOREIGN KEY (id_modele) REFERENCES modeles(id_modele)
) ENGINE=InnoDB;

-- 6. Table des Annonces
CREATE TABLE annonces (
    id_annonce INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_vehicule INT NOT NULL,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    prix DECIMAL(12, 2) NOT NULL,
    statut ENUM('en_attente', 'valide', 'vendu', 'archive') DEFAULT 'en_attente',
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
USE auto_market_db;

-- Ajouter des marques et modèles
INSERT INTO marques (nom_marque) VALUES ('Renault'), ('Dacia'), ('Toyota');
INSERT INTO modeles (nom_modele, id_marque) VALUES ('Clio', 1), ('Logan', 2), ('Yaris', 3);

-- Ajouter un utilisateur (Client)
INSERT INTO utilisateurs (nom, prenom, email, password, telephone, ville, role) 
VALUES ('Boujadi', 'Oussama', 'test@client.ma', '123456', '0600000000', 'Khouribga', 0);

-- Ajouter un véhicule et une annonce VALIDÉE
INSERT INTO vehicules (id_modele, kilometrage, annee, carburant, boite_vitesse) VALUES (1, 120000, 2018, 'Diesel', 'Manuelle');
INSERT INTO annonces (id_user, id_vehicule, titre, description, prix, statut) 
VALUES (1, 1, 'Belle Renault Clio 4', 'Vends Clio 4 en très bon état, bien entretenue.', 95000, 'valide');
-- On insère une voiture déjà validée pour qu'elle s'affiche sur l'accueil
INSERT INTO vehicules (id_modele, kilometrage, annee, carburant, boite_vitesse) VALUES (1, 85000, 2020, 'Diesel', 'Manuelle');
INSERT INTO annonces (id_user, id_vehicule, titre, description, prix, statut) 
VALUES (1, LAST_INSERT_ID(), 'Dacia Logan - État Neuf', 'Une excellente voiture économique.', 105000, 'valide');
CREATE TABLE favoris (
    id_favori INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_annonce INT NOT NULL,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fav_user FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_fav_annonce FOREIGN KEY (id_annonce) REFERENCES annonces(id_annonce) ON DELETE CASCADE
) ENGINE=InnoDB;