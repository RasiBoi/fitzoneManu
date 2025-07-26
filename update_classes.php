<?php
// Define constant to allow inclusion of necessary files
define('FITZONE_APP', true);

// Include configuration and database connection
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Get database connection
$db = getDb();

// First, clear existing classes
$db->query("TRUNCATE TABLE fitness_classes");

// Insert new, well-defined classes
$classes = [
    [
        'name' => 'High-Intensity Interval Training (HIIT)',
        'description' => 'Dynamic workout combining intense bursts of exercise with short recovery periods. Burns fat, improves endurance, and builds strength through varied exercises including cardio and bodyweight movements.',
        'duration' => '45 mins',
        'difficulty' => 'Advanced',
        'trainer' => 'Sarah Parker',
        'schedule_days' => 'Monday, Wednesday, Friday',
        'schedule_times' => '6:30 AM, 5:30 PM',
        'image' => 'hiit.jpg'
    ],
    [
        'name' => 'Power Yoga Flow',
        'description' => 'A vigorous, fitness-based approach to vinyasa-style yoga. Builds strength, flexibility and concentration while challenging your balance and core stability through dynamic flowing movements.',
        'duration' => '60 mins',
        'difficulty' => 'Intermediate',
        'trainer' => 'Michael Chen',
        'schedule_days' => 'Tuesday, Thursday, Saturday',
        'schedule_times' => '7:00 AM, 6:00 PM',
        'image' => 'yoga.jpg'
    ],
    [
        'name' => 'Strength & Sculpt',
        'description' => 'Total body workout focusing on building muscle and improving definition. Uses free weights, resistance bands, and bodyweight exercises to target all major muscle groups.',
        'duration' => '50 mins',
        'difficulty' => 'Intermediate',
        'trainer' => 'James Wilson',
        'schedule_days' => 'Monday, Thursday, Saturday',
        'schedule_times' => '8:00 AM, 4:30 PM',
        'image' => 'sculpt.jpg'
    ]
];

// Insert the new classes
foreach ($classes as $class) {
    $db->query(
        "INSERT INTO fitness_classes (name, description, duration, difficulty, trainer, schedule_days, schedule_times, image) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $class['name'],
            $class['description'],
            $class['duration'],
            $class['difficulty'],
            $class['trainer'],
            $class['schedule_days'],
            $class['schedule_times'],
            $class['image']
        ]
    );
}

echo "Classes updated successfully!";
?>
