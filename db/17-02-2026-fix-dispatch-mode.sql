-- Script de correction pour ajouter le mode 'proportion' à l'ENUM
-- Date: 2026-02-17
-- À exécuter après la structure initiale des tables dispatch

USE db_s2_ETU003945;

-- Modifier la colonne mode pour accepter 'proportion'
ALTER TABLE dispatch_bngrc MODIFY COLUMN mode ENUM('date', 'quantity', 'proportion') NOT NULL;

-- Vérifier que la modification est bien appliquée
DESCRIBE dispatch_bngrc;
ALTER TABLE dispatch_bngrc MODIFY COLUMN mode ENUM('date', 'quantity', 'proportion') NOT NULL;