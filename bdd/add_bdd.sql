-- Alimentation de la table "agences" --

INSERT INTO `touchepasauklaxon`.`agences` (ville) VALUES 
('Paris'),('Lyon'),('Marseille'),('Toulouse'),('Nice'),('Nantes'),('Strasbourg'),('Montpellier')
,('Bordeaux'),('Lille'),('Rennes'),('Reims');

-- Alimentation de la table "users" --

INSERT INTO `touchepasauklaxon`.`users` (nom, prenom, email, password, tel) VALUES 
('Martin','Alexandre','alexandre.martin@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0612345678'),
('Dubois','Sophie','sophie.dubois@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0698765432'),
('Bernard','Julien','julien.bernard@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0622446688'),
('Moreau','Camille','camille.moreau@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0611223344'),
('Lefèvre','Lucie','lucie.lefevre@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0777889900'),
('Leroy','Thomas','thomas.leroy@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0655443322'),
('Roux','Chloé','chloe.roux@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0633221199'),
('Petit','Maxime','maxime.petit@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0766778899'),
('Garnier','Laura','laura.garnier@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0688776655'),
('Dupuis','Antoine','antoine.dupuis@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0744556677'),
('Lefebvre','Emma','emma.lefebvre@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0699887766'),
('Fontaine','Louis','louis.fontaine@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0655667788'),
('Chevalier','Clara','clara.chevalier@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0788990011'),
('Robin','Nicolas','nicolas.robin@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0644332211'),
('Gauthier','Marine','marine.gauthier@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0677889922'),
('Fournier','Pierre','pierre.fournier@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0722334455'),
('Girard','Sarah','sarah.girard@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0688665544'),
('Lambert','Hugo','hugo.lambert@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0611223366'),
('Masson','Julie','julie.masson@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0733445566'),
('Henry','Arthur','arthur.henry@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0666554433'),
('Carril','Ludovic','ludovic.carril@email.fr','$2y$10$ECOTEoMK7a6CpfIUxxVObuK1fAz8jHqXyfTIEo8pehy6AuiBjdCYm','0700112233');

-- Les passwords sont hashées avec bcrypt, vous pouvez les vérifier avec la fonction password_verify() en PHP. 
-- Dans notre exemple, tous les utilisateurs ont le même mot de passe : "azerty123".

-- Mise à jour de l'utilisateur Carril pour le rendre administrateur --
UPDATE `touchepasauklaxon`.`users`
SET `isAdmin` = 1
WHERE `email` = 'ludovic.carril@email.fr';