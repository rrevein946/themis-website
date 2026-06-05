USE themis_db;

INSERT INTO statuses (name, slug, description) VALUES
('Новая', 'new', 'Заявка только что создана и ожидает назначения'),
('В обработке', 'in_progress', 'Специалист изучает вопрос'),
('Подтверждена', 'confirmed', 'Время консультации согласовано'),
('Отклонена', 'rejected', 'Заявка отклонена (не по профилю/загруженность)'),
('Завершена', 'completed', 'Консультация оказана и закрыта');

INSERT INTO categories (name, slug) VALUES
('Гражданское право', 'civil_law'),
('Уголовное право', 'criminal_law'),
('Корпоративное право', 'corporate_law'),
('Семейное право', 'family_law');

INSERT INTO users (email, password_hash, first_name, last_name, phone, role) VALUES
('admin@themis.ru', '$2y$12$jgYXQ2RWhO4hJsx2HKWFVuHBsoPCWTG0JHaandYZcc4zgiu3ySzLa', 'Иван', 'Админов', '+79001112233', 'admin'),
('spec@themis.ru', '$2y$12$jgYXQ2RWhO4hJsx2HKWFVuHBsoPCWTG0JHaandYZcc4zgiu3ySzLa', 'Мария', 'Юридова', '+79004445566', 'specialist'),
('user@themis.ru', '$2y$12$jgYXQ2RWhO4hJsx2HKWFVuHBsoPCWTG0JHaandYZcc4zgiu3ySzLa', 'Алексей', 'Клиентов', '+79007778899', 'user');

INSERT INTO services (category_id, title, description, price, is_active) VALUES
(1, 'Консультация по гражданским делам', 'Разбор договоров, исков, претензий, досудебное урегулирование', 5000.00, 1),
(2, 'Защита по уголовным делам', 'Сопровождение на стадии следствия и в суде', 15000.00, 1),
(3, 'Регистрация ООО/ИП', 'Подготовка документов, подача в ФНС, открытие счета', 8000.00, 1),
(4, 'Расторжение брака', 'Помощь в оформлении развода и разделе имущества', 7000.00, 1);

INSERT INTO applications (user_id, service_id, specialist_id, status_id, client_message, specialist_response) VALUES
(3, 1, 2, 2, 'Нужна помощь с составлением договора аренды коммерческого помещения.', 'Документы получены, готовлю проект договора. Срок: 2 дня.'),
(3, 4, NULL, 1, 'Хочу расторгнуть брак в одностороннем порядке без суда.', NULL);
