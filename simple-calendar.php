<?php
/*
Plugin Name: Simple Calendar
Description: A simple calendar plugin for managing events.
Version: 1.0.0
Author: Devlyn Chelin
*/

// Create database table on plugin activation
function create_calendar_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'calendar_events';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        event_date date NOT NULL,
        event_title varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_calendar_table');

// Add menu item for calendar in admin panel
function add_calendar_menu_item() {
    add_menu_page(
        'Calendar Events',
        'Calendar Events',
        'manage_options',
        'calendar-events',
        'calendar_events_page'
    );
}

add_action('admin_menu', 'add_calendar_menu_item');

// Render calendar events page
function calendar_events_page() {
    ?>
    <div class="wrap">
        <h1>Calendar Events</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="add_calendar_event">
            <label for="event_date">Event Date:</label>
            <input type="date" id="event_date" name="event_date" required>
            <label for="event_title">Event Title:</label>
            <input type="text" id="event_title" name="event_title" required>
            <input type="submit" value="Add Event">
        </form>
        <h2>Existing Events</h2>
        <?php display_calendar_events(); ?>
    </div>
    <?php
}

// Add calendar event
function add_calendar_event() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'calendar_events';
    $event_date = $_POST['event_date'];
    $event_title = sanitize_text_field($_POST['event_title']);
    $wpdb->insert(
        $table_name,
        array(
            'event_date' => $event_date,
            'event_title' => $event_title,
        ),
        array(
            '%s',
            '%s',
        )
    );
    wp_redirect(admin_url('admin.php?page=calendar-events'));
    exit;
}

add_action('admin_post_add_calendar_event', 'add_calendar_event');

// Display calendar events
function display_calendar_events() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'calendar_events';
    $events = $wpdb->get_results("SELECT * FROM $table_name ORDER BY event_date ASC");
    if ($events) {
        echo '<ul>';
        foreach ($events as $event) {
            echo '<li>' . date('F j, Y', strtotime($event->event_date)) . ' - ' . esc_html($event->event_title) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No events found.</p>';
    }
}
