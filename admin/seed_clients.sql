-- Seed Fictional Clients and Quotes for Content Catalogz CRM

-- Client 1: Tech Startup - Sarah Chen
INSERT INTO quotes (name, company, email, phone, address_street, address_city, address_county, address_postcode, address_country, services, total_cost, total_paid, status, message, lead_source, expected_value, probability, next_follow_up, last_contact_date) VALUES (
    'Sarah Chen',
    'TechVision AI',
    'sarah.chen@techvision-ai.com',
    '+44 20 7946 0958',
    '45 Silicon Street',
    'London',
    'Greater London',
    'EC2A 4DP',
    'United Kingdom',
    '[{"name":"Corporate Website Design","cost":"4500"},{"name":"SEO Optimization","cost":"1200"},{"name":"Brand Identity Package","cost":"2800"}]',
    8500.00,
    3000.00,
    'in_progress',
    'We need a modern, AI-focused corporate website that showcases our machine learning solutions. Looking for clean design with interactive demos and case studies. Must be mobile-responsive and optimized for conversions.',
    'LinkedIn Referral',
    8500.00,
    85,
    '2026-02-14',
    '2026-02-05'
);

-- Client 2: Restaurant Owner - Marco Rossi
INSERT INTO quotes (name, company, email, phone, address_street, address_city, address_county, address_postcode, address_country, services, total_cost, total_paid, status, lead_source, expected_value, probability, next_follow_up, last_contact_date) VALUES (
    'Marco Rossi',
    'La Bella Vista Restaurant',
    'marco@labellavista.co.uk',
    '+44 161 496 0234',
    '12 Market Square',
    'Manchester',
    'Greater Manchester',
    'M1 1JQ',
    'United Kingdom',
    '[{"name":"Restaurant Website","cost":"2800"},{"name":"Online Booking System","cost":"1500"},{"name":"Menu Photography","cost":"650"}]',
    4950.00,
    4950.00,
    'completed',
    'Google Search',
    4950.00,
    100,
    NULL,
    '2026-01-28'
);

-- Client 3: Fitness Business - Amanda Foster
INSERT INTO quotes (name, company, email, phone, address_street, address_city, address_county, address_postcode, address_country, services, total_cost, total_paid, status, lead_source, expected_value, probability, next_follow_up, last_contact_date) VALUES (
    'Amanda Foster',
    'FitLife Gym & Wellness',
    'amanda@fitlifegym.com',
    '+44 113 496 0876',
    '78 Wellness Avenue',
    'Leeds',
    'West Yorkshire',
    'LS1 4BZ',
    'United Kingdom',
    '[{"name":"Gym Website with Class Booking","cost":"3500"},{"name":"Mobile App Development","cost":"8500"},{"name":"Social Media Campaign","cost":"1800"}]',
    13800.00,
    0.00,
    'quoted',
    'Trade Show',
    13800.00,
    45,
    '2026-02-10',
    '2026-02-01'
);

-- Client 4: Law Firm - James Whitmore
INSERT INTO quotes (name, company, email, phone, address_street, address_city, address_county, address_postcode, address_country, services, total_cost, total_paid, status, lead_source, expected_value, probability, next_follow_up, last_contact_date) VALUES (
    'James Whitmore',
    'Whitmore & Associates Legal',
    'j.whitmore@whitmorelegal.co.uk',
    '+44 20 7123 4567',
    '88 Chancery Lane',
    'London',
    'Greater London',
    'WC2A 1DD',
    'United Kingdom',
    '[{"name":"Professional Law Firm Website","cost":"5500"},{"name":"Client Portal Development","cost":"4200"},{"name":"SSL & Security Setup","cost":"450"}]',
    10150.00,
    5000.00,
    'in_progress',
    'Client Referral',
    10150.00,
    90,
    '2026-02-12',
    '2026-02-06'
);

-- Client 5: E-commerce Startup - Olivia Martinez
INSERT INTO quotes (name, company, email, phone, address_street, address_city, address_county, address_postcode, address_country, services, total_cost, total_paid, status, lead_source, expected_value, probability, next_follow_up, last_contact_date) VALUES (
    'Olivia Martinez',
    'EcoThreads Sustainable Fashion',
    'olivia@ecothreads.shop',
    '+44 117 924 5678',
    '34 Green Park Road',
    'Bristol',
    'Bristol',
    'BS1 5AH',
    'United Kingdom',
    '[{"name":"E-commerce Website","cost":"6800"},{"name":"Payment Gateway Integration","cost":"1200"},{"name":"Product Photography","cost":"950"},{"name":"Email Marketing Setup","cost":"750"}]',
    9700.00,
    2000.00,
    'in_progress',
    'Instagram Ad',
    9700.00,
    70,
    '2026-02-09',
    '2026-02-04'
);

-- Add some activities for the clients
INSERT INTO activities (client_id, activity_type, subject, description, activity_date, duration_minutes, created_by) VALUES
-- Sarah Chen activities
(1, 'call', 'Initial consultation call', 'Discussed project requirements and timeline. Client wants modern design with AI integration focus.', '2026-01-15 14:30:00', 45, 1),
(1, 'email', 'Sent design mockups', 'Forwarded 3 homepage design concepts for review', '2026-01-22 10:15:00', NULL, 1),
(1, 'meeting', 'Design review meeting', 'Client approved Design 2 with minor adjustments to color scheme', '2026-01-28 15:00:00', 60, 1),
(1, 'payment_received', 'Deposit payment received', '£3,000 deposit paid via bank transfer', '2026-02-01 09:30:00', NULL, 1),
(1, 'email', 'Development progress update', 'Sent link to staging site for review', '2026-02-05 16:45:00', NULL, 1),

-- Marco Rossi activities
(2, 'call', 'Follow-up call', 'Discussed menu integration and photo requirements', '2026-01-10 11:00:00', 30, 1),
(2, 'meeting', 'On-site photography session', 'Photographed restaurant interior and signature dishes', '2026-01-15 13:00:00', 180, 1),
(2, 'quote_sent', 'Final quote sent', 'Sent itemized quote for website and booking system', '2026-01-18 09:00:00', NULL, 1),
(2, 'payment_received', 'Full payment received', 'Project completed and paid in full', '2026-01-28 14:20:00', NULL, 1),

-- Amanda Foster activities
(3, 'email', 'Initial inquiry', 'Client interested in website and mobile app', '2026-01-25 08:30:00', NULL, 1),
(3, 'call', 'Discovery call', 'Discussed features needed for class booking and member portal', '2026-01-28 10:00:00', 40, 1),
(3, 'quote_sent', 'Comprehensive quote sent', 'Sent detailed proposal with timeline and milestones', '2026-02-01 11:30:00', NULL, 1),

-- James Whitmore activities
(4, 'meeting', 'In-person consultation', 'Met at office to discuss compliance requirements and security needs', '2026-01-20 14:00:00', 90, 1),
(4, 'email', 'Contract and timeline sent', 'Forwarded project contract and development schedule', '2026-01-23 09:15:00', NULL, 1),
(4, 'payment_received', 'Initial payment received', '£5,000 received to begin development', '2026-01-25 10:00:00', NULL, 1),
(4, 'call', 'Progress check-in', 'Reviewed homepage design and discussed content strategy', '2026-02-03 16:00:00', 35, 1),
(4, 'email', 'Content request', 'Requested bio photos and case study information', '2026-02-06 10:30:00', NULL, 1),

-- Olivia Martinez activities
(5, 'email', 'Instagram inquiry', 'Reached out via Instagram ad campaign', '2026-01-18 15:20:00', NULL, 1),
(5, 'call', 'Initial consultation', 'Discussed e-commerce needs and sustainable branding', '2026-01-22 11:30:00', 50, 1),
(5, 'meeting', 'Product photography planning', 'Reviewed product line and planned photoshoot', '2026-01-29 13:00:00', 60, 1),
(5, 'payment_received', 'Deposit received', '£2,000 deposit for project kickoff', '2026-02-02 09:00:00', NULL, 1),
(5, 'email', 'Platform selection discussion', 'Sent comparison of Shopify vs WooCommerce', '2026-02-04 14:15:00', NULL, 1);

-- Add some notes for the clients
INSERT INTO client_notes (client_id, note_text, is_important, created_by) VALUES
(1, 'Client prefers communication via email. Available for calls only on Tuesdays and Thursdays after 2pm.', 0, 1),
(1, 'URGENT: Launch deadline is March 15th for major investor presentation. Team working on tight timeline.', 1, 1),
(2, 'Very happy with the service. Mentioned he has connections with other restaurant owners who might need websites.', 0, 1),
(2, 'Asked about maintenance packages for menu updates. Follow up with monthly support options.', 0, 1),
(3, 'Budget conscious - needs to see ROI before committing to mobile app. Start with website first, discuss app later.', 1, 1),
(3, 'Had bad experience with previous developer who disappeared mid-project. Wants regular updates and clear communication.', 1, 1),
(4, 'Very detail-oriented. Expects professional, formal communication. All changes must go through approval process.', 0, 1),
(4, 'Law society compliance requirements for solicitor websites must be met. Double-check all regulatory aspects.', 1, 1),
(5, 'Passionate about sustainability - wants carbon-neutral hosting and eco-friendly design practices.', 0, 1),
(5, 'Plans to launch with 50 products initially, scaling to 200+ within 6 months. Ensure platform can handle growth.', 1, 1);

-- Add some tasks
INSERT INTO tasks (title, description, client_id, priority, status, due_date, created_by) VALUES
('Complete TechVision AI homepage design', 'Finalize homepage layout with approved color scheme changes', 1, 'high', 'in_progress', '2026-02-09', 1),
('Set up staging environment for TechVision', 'Configure staging server and deploy current build for client review', 1, 'urgent', 'pending', '2026-02-08', 1),
('Follow up with Amanda on quote', 'Check if she has any questions about the proposal. Address budget concerns.', 3, 'high', 'pending', '2026-02-10', 1),
('Gather content from Whitmore Legal', 'Need lawyer bios, case studies, and office photos', 4, 'medium', 'pending', '2026-02-11', 1),
('Product photo shoot for EcoThreads', 'Schedule and conduct product photography session for initial 50 items', 5, 'high', 'pending', '2026-02-09', 1),
('Set up WooCommerce for EcoThreads', 'Install and configure WooCommerce with sustainable shipping plugins', 5, 'medium', 'pending', '2026-02-12', 1),
('Monthly check-in with La Bella Vista', 'Touch base with Marco about website performance and any updates needed', 2, 'low', 'pending', '2026-02-20', 1),
('Prepare investor presentation materials', 'Create PDF deck showcasing TechVision AI project for their investor meeting', 1, 'urgent', 'pending', '2026-02-13', 1);

SELECT 'Successfully created 5 fictional clients with quotes, activities, notes, and tasks!' AS status;
