<?php
/**
 * Generate 25 random clients with comprehensive data
 * Run with: php admin/setup/generate_clients.php
 */

if (php_sapi_name() !== 'cli') {
    // web request - require authentication
    session_start();
    require_once __DIR__ . '/../config/auth.php';
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo "403 - authentication required\n";
        exit;
    }
}

require_once __DIR__ . '/../config/db.php';

echo "Generating 25 random clients with comprehensive data...\n";

// Arrays for random data generation
$firstNames = ['James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Margaret', 'Thomas', 'Dorothy', 'Charles', 'Lisa', 'Christopher', 'Nancy', 'Daniel', 'Karen', 'Matthew', 'Betty', 'Anthony', 'Helen', 'Mark', 'Sandra', 'Donald', 'Donna', 'Steven', 'Carol', 'Paul', 'Ruth', 'Andrew', 'Sharon', 'Joshua', 'Michelle', 'Kenneth', 'Laura', 'Kevin', 'Sarah', 'Brian', 'Kimberly', 'George', 'Deborah', 'Timothy', 'Dorothy', 'Ronald', 'Jessica', 'Edward', 'Shirley', 'Jason', 'Angela', 'Gary', 'Melissa', 'Nicholas', 'Brenda', 'Eric', 'Amy', 'Jonathan', 'Anna', 'Stephen', 'Rebecca', 'Larry', 'Virginia', 'Justin', 'Kathleen', 'Scott', 'Pamela', 'Brandon', 'Martha', 'Benjamin', 'Debra', 'Samuel', 'Amanda', 'Gregory', 'Stephanie', 'Alexander', 'Carolyn', 'Patrick', 'Christine', 'Jack', 'Marie', 'Dennis', 'Janet', 'Jerry', 'Catherine', 'Tyler', 'Frances', 'Aaron', 'Ann', 'Jose', 'Joyce', 'Adam', 'Diane', 'Nathan', 'Alice', 'Henry', 'Julie', 'Zachary', 'Heather', 'Douglas', 'Teresa', 'Peter', 'Doris', 'Kyle', 'Gloria', 'Noah', 'Evelyn', 'Ethan', 'Jean', 'Jeremy', 'Cheryl', 'Walter', 'Mildred', 'Christian', 'Katherine', 'Keith', 'Joan', 'Roger', 'Ashley', 'Terry', 'Judith', 'Austin', 'Rose', 'Sean', 'Janice', 'Gerald', 'Kelly', 'Carl', 'Nicole', 'Harold', 'Judy', 'Christian', 'Christina', 'Arthur', 'Kathy', 'Ryan', 'Theresa', 'Lawrence', 'Beverly', 'Jesse', 'Denise', 'Willie', 'Tammy', 'Billy', 'Irene', 'Joe', 'Jane', 'Danny', 'Lori', 'Phillip', 'Rachel', 'Ralph', 'Marilyn', 'Bryan', 'Andrea', 'Harry', 'Kathryn', 'Bruce', 'Louise', 'Wayne', 'Sara', 'Eugene', 'Anne', 'Louis', 'Jacqueline', 'Alan', 'Wanda', 'Juan', 'Bonnie', 'Fred', 'Julia', 'Jack', 'Ruby', 'Albert', 'Lois', 'Jonathan', 'Tina', 'Justin', 'Phyllis', 'Terry', 'Norma', 'Gerald', 'Paula', 'Keith', 'Diana', 'Samuel', 'Annie', 'Willie', 'Lillian', 'Brandon', 'Emily', 'Harry', 'Robin'];

$lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts', 'Gomez', 'Phillips', 'Evans', 'Turner', 'Diaz', 'Parker', 'Cruz', 'Edwards', 'Collins', 'Reyes', 'Stewart', 'Morris', 'Morales', 'Murphy', 'Cook', 'Rogers', 'Gutierrez', 'Ortiz', 'Morgan', 'Cooper', 'Peterson', 'Bailey', 'Reed', 'Kelly', 'Howard', 'Ramos', 'Kim', 'Cox', 'Ward', 'Richardson', 'Watson', 'Brooks', 'Chavez', 'Wood', 'James', 'Bennett', 'Gray', 'Mendoza', 'Ruiz', 'Hughes', 'Price', 'Alvarez', 'Castillo', 'Sanders', 'Patel', 'Myers', 'Long', 'Ross', 'Foster', 'Jimenez'];

$companies = ['Tech Solutions Ltd', 'Digital Marketing Co', 'Creative Agency', 'Web Design Studio', 'Marketing Experts', 'Brand Builders', 'Content Creators', 'Social Media Pros', 'SEO Specialists', 'Graphic Design Hub', 'Video Production Co', 'Photography Studio', 'Event Management', 'PR Consultants', 'Advertising Agency', 'Media Production', 'Design Studio', 'Digital Agency', 'Marketing Solutions', 'Creative Collective', 'Brand Agency', 'Content Marketing', 'Social Media Agency', 'Web Development', 'App Development', 'E-commerce Solutions', 'Business Consulting', 'Strategy Advisors', 'Growth Hackers', 'Innovation Labs'];

$streets = ['High Street', 'Church Road', 'London Road', 'Victoria Road', 'Kings Road', 'Queen Street', 'Park Lane', 'Oxford Street', 'Baker Street', 'Regent Street', 'Bond Street', 'Carnaby Street', 'Portobello Road', 'Brick Lane', 'Shoreditch High Street', 'Camden High Street', 'Kingsland Road', 'Dalston Lane', 'Hackney Road', 'Whitechapel Road', 'Commercial Street', 'Old Street', 'City Road', 'Upper Street', 'Kentish Town Road', 'Finchley Road', 'Hampstead High Street', 'Belsize Park', 'Swiss Cottage', 'St John\'s Wood'];

$cities = ['London', 'Manchester', 'Birmingham', 'Leeds', 'Liverpool', 'Newcastle', 'Sheffield', 'Bristol', 'Nottingham', 'Leicester', 'Coventry', 'Bradford', 'Hull', 'Plymouth', 'Stoke-on-Trent', 'Wolverhampton', 'Derby', 'Swansea', 'Southampton', 'Salford', 'Aberdeen', 'Dundee', 'Edinburgh', 'Glasgow', 'Cardiff', 'Belfast'];

$counties = ['Greater London', 'West Midlands', 'West Yorkshire', 'Greater Manchester', 'Merseyside', 'Tyne and Wear', 'South Yorkshire', 'West Midlands', 'Avon', 'Nottinghamshire', 'Leicestershire', 'West Midlands', 'Humberside', 'Devon', 'Staffordshire', 'West Midlands', 'Derbyshire', 'West Glamorgan', 'Hampshire', 'Greater Manchester', 'Aberdeen City', 'Dundee City', 'City of Edinburgh', 'Glasgow City', 'Cardiff', 'Belfast'];

$services = [
    ['name' => 'Website Design', 'cost' => 2500],
    ['name' => 'Website Development', 'cost' => 3500],
    ['name' => 'E-commerce Setup', 'cost' => 4500],
    ['name' => 'SEO Optimization', 'cost' => 1200],
    ['name' => 'Social Media Marketing', 'cost' => 800],
    ['name' => 'Content Creation', 'cost' => 600],
    ['name' => 'Brand Identity Design', 'cost' => 1800],
    ['name' => 'Logo Design', 'cost' => 800],
    ['name' => 'Video Production', 'cost' => 3000],
    ['name' => 'Photography', 'cost' => 1200],
    ['name' => 'Email Marketing', 'cost' => 500],
    ['name' => 'PPC Advertising', 'cost' => 1500],
    ['name' => 'Mobile App Development', 'cost' => 8000],
    ['name' => 'CRM Setup', 'cost' => 2200],
    ['name' => 'Analytics Setup', 'cost' => 900],
    ['name' => 'Consultation', 'cost' => 300],
    ['name' => 'Strategy Development', 'cost' => 1500],
    ['name' => 'Training Session', 'cost' => 400],
    ['name' => 'Maintenance Package', 'cost' => 600],
    ['name' => 'Custom Development', 'cost' => 5000]
];

$leadSources = ['Website', 'Referral', 'Social Media', 'Email Marketing', 'Direct Contact', 'Other'];
$statuses = ['new', 'in_progress', 'completed', 'cancelled'];

$notes = [
    'Client is very enthusiastic about the project.',
    'Requested a follow-up call next week.',
    'Budget constraints - need to discuss pricing.',
    'Competitor analysis required before proceeding.',
    'Client has specific brand guidelines to follow.',
    'Timeline is tight - expedited delivery needed.',
    'Multiple stakeholders involved in decision making.',
    'Previous experience with similar projects.',
    'Looking for ongoing maintenance agreement.',
    'Interested in additional services after completion.',
    'Technical requirements need clarification.',
    'Client prefers modern, minimalist design.',
    'Budget approved - ready to proceed.',
    'Waiting for final approval from management.',
    'Client has questions about deliverables.',
    'Previous work with similar clients.',
    'Strong focus on ROI and results.',
    'Client is tech-savvy and detail-oriented.',
    'Looking for innovative solutions.',
    'Client has specific integration requirements.'
];

$taskTitles = [
    'Initial consultation call',
    'Requirements gathering',
    'Proposal preparation',
    'Contract review',
    'Design mockups',
    'Client feedback review',
    'Development phase',
    'Testing and QA',
    'Content creation',
    'SEO optimization',
    'Social media setup',
    'Training session',
    'Final delivery',
    'Payment collection',
    'Follow-up call'
];

$taskDescriptions = [
    'Schedule and conduct initial discovery call with client.',
    'Gather detailed requirements and project specifications.',
    'Prepare comprehensive project proposal and pricing.',
    'Review contract terms and obtain signatures.',
    'Create initial design concepts and wireframes.',
    'Present designs and gather client feedback.',
    'Begin development work on approved designs.',
    'Test functionality and ensure quality standards.',
    'Create content for website and marketing materials.',
    'Implement SEO best practices and optimization.',
    'Set up social media accounts and profiles.',
    'Conduct training on new systems and processes.',
    'Deliver final project files and documentation.',
    'Process final payment and close project.',
    'Follow up with client to ensure satisfaction.'
];

$taskPriorities = ['low', 'medium', 'high', 'urgent'];
$taskStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];

function generateRandomPhone() {
    $prefixes = ['020', '0161', '0121', '0113', '0151', '0191', '0114', '0117', '0115', '0116'];
    $prefix = $prefixes[array_rand($prefixes)];
    $number = str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
    return $prefix . ' ' . substr($number, 0, 3) . ' ' . substr($number, 3);
}

function generateRandomPostcode() {
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $outward = $letters[rand(0, 25)] . $letters[rand(0, 25)] . rand(0, 9) . rand(0, 9);
    $inward = rand(0, 9) . $letters[rand(0, 25)] . $letters[rand(0, 25)];
    return $outward . ' ' . $inward;
}

function generateRandomEmail($firstName, $lastName) {
    $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'company.com', 'business.co.uk', 'email.com'];
    $first = strtolower($firstName);
    $last = strtolower($lastName);
    $domain = $domains[array_rand($domains)];

    $formats = [
        $first . '.' . $last . '@' . $domain,
        $first . $last . '@' . $domain,
        $first[0] . $last . '@' . $domain,
        $first . '_' . $last . '@' . $domain
    ];

    return $formats[array_rand($formats)];
}

// Generate 25 clients
for ($i = 0; $i < 25; $i++) {
    // Basic client info
    $firstName = $firstNames[array_rand($firstNames)];
    $lastName = $lastNames[array_rand($lastNames)];
    $name = $firstName . ' ' . $lastName;
    $company = rand(0, 2) ? $companies[array_rand($companies)] : null; // 2/3 chance of having company
    $email = generateRandomEmail($firstName, $lastName);
    $phone = generateRandomPhone();

    // Address
    $addressStreet = rand(1, 999) . ' ' . $streets[array_rand($streets)];
    $addressLine2 = rand(0, 1) ? null : 'Flat ' . rand(1, 50);
    $addressCity = $cities[array_rand($cities)];
    $addressCounty = $counties[array_rand($counties)];
    $addressPostcode = generateRandomPostcode();
    $addressCountry = 'United Kingdom';

    // Services (1-4 services per client)
    $numServices = rand(1, 4);
    $clientServices = [];
    $totalCost = 0;

    $availableServices = $services;
    shuffle($availableServices);

    for ($j = 0; $j < $numServices; $j++) {
        $service = $availableServices[$j];
        // Add some variation to pricing (±20%)
        $variation = rand(-20, 20) / 100;
        $serviceCost = round($service['cost'] * (1 + $variation), 2);
        $clientServices[] = [
            'name' => $service['name'],
            'cost' => $serviceCost
        ];
        $totalCost += $serviceCost;
    }

    // Financial data
    $paymentStatus = rand(0, 4); // 0-4 for different payment scenarios
    switch ($paymentStatus) {
        case 0: // Not paid
            $totalPaid = 0.00;
            break;
        case 1: // Partially paid
            $totalPaid = round($totalCost * (rand(25, 75) / 100), 2);
            break;
        case 2: // Fully paid
            $totalPaid = $totalCost;
            break;
        case 3: // Overpaid
            $totalPaid = round($totalCost * (1 + rand(5, 20) / 100), 2);
            break;
        case 4: // Deposit only
            $totalPaid = round($totalCost * (rand(20, 40) / 100), 2);
            break;
    }
    $totalRemaining = round($totalCost - $totalPaid, 2);

    // Other data
    $leadSource = $leadSources[array_rand($leadSources)];
    $status = $statuses[array_rand($statuses)];
    $message = rand(0, 1) ? 'Initial inquiry about ' . implode(', ', array_column($clientServices, 'name')) : 'General inquiry about services';
    $notesText = rand(0, 2) ? $notes[array_rand($notes)] : null; // 1/3 chance of having notes

    // Insert client
    $servicesJson = json_encode($clientServices);

    $stmt = $conn->prepare("INSERT INTO quotes (name, first_name, last_name, email, company, phone, address_street, address_line2, address_city, address_county, address_postcode, address_country, message, services, total_cost, total_paid, total_remaining, lead_source, status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error . "\n";
        continue;
    }

    $stmt->bind_param("ssssssssssssssdddsss",
        $name, $firstName, $lastName, $email, $company, $phone,
        $addressStreet, $addressLine2, $addressCity, $addressCounty, $addressPostcode, $addressCountry,
        $message, $servicesJson, $totalCost, $totalPaid, $totalRemaining,
        $leadSource, $status, $notesText
    );

    if ($stmt->execute()) {
        $clientId = $conn->insert_id;
        echo "Created client: $name (ID: $clientId)\n";

        // Generate tasks (0-3 tasks per client)
        $numTasks = rand(0, 3);
        if ($numTasks > 0) {
            for ($k = 0; $k < $numTasks; $k++) {
                $taskTitle = $taskTitles[array_rand($taskTitles)];
                $taskDescription = $taskDescriptions[array_rand($taskDescriptions)];
                $taskPriority = $taskPriorities[array_rand($taskPriorities)];
                $taskStatus = $taskStatuses[array_rand($taskStatuses)];
                $dueDate = date('Y-m-d', strtotime('+' . rand(1, 30) . ' days'));

                $taskStmt = $conn->prepare("INSERT INTO tasks (client_id, title, description, status, priority, due_date, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $taskStmt->bind_param("isssssii", $clientId, $taskTitle, $taskDescription, $taskStatus, $taskPriority, $dueDate, rand(1, 5), rand(1, 5)); // Random user IDs
                $taskStmt->execute();
                $taskStmt->close();
            }
        }

        // Generate activities (0-2 activities per client)
        $numActivities = rand(0, 2);
        if ($numActivities > 0) {
            $activityTypes = ['call', 'email', 'meeting', 'note'];
            $activitySubjects = ['Initial Contact', 'Follow-up', 'Project Discussion', 'Quote Sent', 'Feedback Received', 'Status Update'];
            $activityDescriptions = [
                'Discussed project requirements and timeline.',
                'Sent detailed proposal via email.',
                'Client requested additional information.',
                'Scheduled follow-up meeting.',
                'Received positive feedback on proposal.',
                'Updated project status and next steps.',
                'Client approved project scope.',
                'Discussed pricing and payment terms.',
                'Provided technical specifications.',
                'Client requested timeline adjustment.'
            ];

            for ($m = 0; $m < $numActivities; $m++) {
                $activityType = $activityTypes[array_rand($activityTypes)];
                $activitySubject = $activitySubjects[array_rand($activitySubjects)];
                $activityDescription = $activityDescriptions[array_rand($activityDescriptions)];
                $activityDate = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days'));

                $activityStmt = $conn->prepare("INSERT INTO activities (client_id, type, subject, description, activity_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                $activityStmt->bind_param("issssi", $clientId, $activityType, $activitySubject, $activityDescription, $activityDate, rand(1, 5));
                $activityStmt->execute();
                $activityStmt->close();
            }
        }

    } else {
        echo "Error creating client $name: " . $stmt->error . "\n";
    }

    $stmt->close();
}

echo "\n✅ Successfully generated 25 random clients with comprehensive data!\n";
echo "Each client includes:\n";
echo "- Basic contact information\n";
echo "- Complete address details\n";
echo "- Multiple services with pricing\n";
echo "- Various payment statuses and balances\n";
echo "- Lead source tracking\n";
echo "- Quote status\n";
echo "- Random notes\n";
echo "- Associated tasks (0-3 per client)\n";
echo "- Activity history (0-2 activities per client)\n";

$conn->close();
?>