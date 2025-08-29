
-- -----------------------------------------------------
-- Table `training_requests_comments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `training_requests_comments` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `training_request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `comments` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

--
-- Filtros para la tabla `training_requests_comments`
--
ALTER TABLE `training_requests_comments`
  ADD CONSTRAINT `training_requests_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `training_requests_comments_training_request_id_foreign` FOREIGN KEY (`training_request_id`) REFERENCES `training_requests` (`id`);

-- -----------------------------------------------------
-- Table `training_requests_logs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `training_requests_logs` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `training_request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `before_status_id` bigint(20) UNSIGNED NOT NULL,
  `after_status_id` bigint(20) UNSIGNED NOT NULL,
  `comments` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

--
-- Filtros para la tabla `training_requests_logs`
--
ALTER TABLE `training_requests_logs`
  ADD CONSTRAINT `training_requests_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `training_requests_logs_before_status_id_foreign` FOREIGN KEY (`before_status_id`) REFERENCES `parameters` (`id`),
  ADD CONSTRAINT `training_requests_logs_after_status_id_foreign` FOREIGN KEY (`after_status_id`) REFERENCES `parameters` (`id`),
  ADD CONSTRAINT `training_requests_logs_training_request_id_foreign` FOREIGN KEY (`training_request_id`) REFERENCES `training_requests` (`id`);

ALTER TABLE `training_requests`
  ADD CONSTRAINT `training_requests_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `parameters` (`id`);

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `table` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'La tabla a la que enlaza ',
  `table_id` int(11) DEFAULT NULL COMMENT 'Combinando tabla y ID identificamo la sección al cual corresponde el documento cargado. Ejemplo: table:Course table_id:5 el documento fue cargado en un curso correspondiente al id 5. ',
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombres del documento',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'El tipo de documentos, estos tipos son obtenidos del archivo seleccionado, permitidos(PDF / Image / Word)',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta donde se encuentra el documento',
  `data` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Para almacenar datos adicionales',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contiene la referencia a todo los documentos cargados en diferentes procesos.  Utiliza como referencia el campo tabla para identificar su correspondiente lugar y enlace. ';


-- -----------------------------------------------------
-- Table `constancies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `constancies` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `training_request_id` bigint(20) UNSIGNED NOT NULL,
  `comments` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Para guardar las constancias de la capacitación';

ALTER TABLE `training_requests_logs`
  ADD CONSTRAINT `constancies_training_request_id_foreign` FOREIGN KEY (`training_request_id`) REFERENCES `training_requests` (`id`);

-- -----------------------------------------------------
-- Table `training_request_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `training_request_users` (
  `training_request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `training_request_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Para guardar la relación entre usuarios y la capaciotación externa';

ALTER TABLE `training_request_users`
  ADD CONSTRAINT `training_request_users_request_id_foreign` FOREIGN KEY (`training_request_id`) REFERENCES `training_requests` (`id`);
ALTER TABLE `training_request_users`
  ADD CONSTRAINT `training_request_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `courses` ADD `status_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Se relaciona con la tabla parameters' AFTER `training_request_id`;
ALTER TABLE `courses` ADD CONSTRAINT `courses_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `parameters` (`id`);
ALTER TABLE `courses` CHANGE `code` `code` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
ALTER TABLE `courses` CHANGE `name` `name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
ALTER TABLE `courses` ADD `specialty_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Se relaciona con la tabla specialities' AFTER `provider_id`;
ALTER TABLE `courses` ADD CONSTRAINT `courses_specialty_id_foreign` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`);
ALTER TABLE `courses` ADD `required` VARCHAR(1) NOT NULL COMMENT 'S: si, N: no' AFTER `status_id`;
ALTER TABLE `courses` CHANGE `training_request_id` `training_request_id` BIGINT(20) UNSIGNED NULL;
ALTER TABLE `courses` CHANGE `start_date` `start_date` DATE NOT NULL;
ALTER TABLE `courses` CHANGE `end_date` `end_date` DATE NOT NULL;

ALTER TABLE `user_courses` CHANGE `attend_how` `attend_how` VARCHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT 'S:Student, T:Teacher';
ALTER TABLE `user_courses` CHANGE `qualification` `qualification` DOUBLE(8,2) NULL;
ALTER TABLE `user_courses` CHANGE `hours` `hours` INT(11) NULL;
ALTER TABLE `user_courses` CHANGE `objective_id` `objective_id` BIGINT(20) UNSIGNED NULL;

-- -----------------------------------------------------
-- Table `user_specialties`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_specialties` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `specialty_id` bigint(20) UNSIGNED NOT NULL,  
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `specialty_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Para guardar la relación entre usuarios y las especialidades';

ALTER TABLE `user_specialties`
  ADD CONSTRAINT `user_specialties_specialty_id_foreign` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`);
ALTER TABLE `user_specialties`
  ADD CONSTRAINT `user_specialties_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

-- -----------------------------------------------------
-- Table `cycles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `cycles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombres del ciclo',
  `start_date` DATE NOT NULL COMMENT 'Fecha inicio del ciclo',
  `end_date` DATE NOT NULL COMMENT 'Fecha fin del ciclo',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Para guardar la configuración de las fechas de cada ciclo';

-- -----------------------------------------------------
-- Table `objective_specialties`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `objective_specialties` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cycle_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Ciclo',
  `specialty_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Especialidad',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Para guardar los objetivos por especialidad';

ALTER TABLE `objective_specialties`
  ADD CONSTRAINT `objective_specialties_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`);

ALTER TABLE `objective_specialties`
  ADD CONSTRAINT `objective_specialties_specialty_id_foreign` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`);

ALTER TABLE `objective_specialties` ADD `hours` INT(11) NULL AFTER `specialty_id`;

-- Ciclos
INSERT INTO `cycles` (`id`, `name`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES (NULL, '2021 - 2022', '2021-08-01', '2022-07-31', current_timestamp(), current_timestamp());
INSERT INTO `cycles` (`id`, `name`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES (NULL, '2022 - 2023', '2022-08-01', '2023-07-31', current_timestamp(), current_timestamp());

-- Data
INSERT INTO `objective_specialties` (`id`, `cycle_id`, `specialty_id`, `hours`, `created_at`, `updated_at`) VALUES (NULL, '1', '1', '40', current_timestamp(), current_timestamp()), (NULL, '2', '3', '30', current_timestamp(), current_timestamp());

-- new
ALTER TABLE `training_requests`
  ADD CONSTRAINT `training_requests_create_user_id_foreign` FOREIGN KEY (`create_user_id`) REFERENCES `users` (`id`);