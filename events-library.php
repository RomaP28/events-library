<?php
/**
 * -------------------------------Library Events----------------------------------
 */
function isAdmin() {
    return current_user_can('manage_options');
}

function user_type() {
    $member = new MeprUser(get_current_user_id());
    $type = $member->get_active_subscription_titles();
    if (str_contains($type, 'Gold')) return 'GOLD';
}

function get_cards($eventFields, $event) {
    if(isset($eventFields['thumbnail']) && !empty($eventFields['thumbnail'])) { ?>
        <div class="event-card">
            <p><?php echo get_the_title( $event ) ?></p>
            <a href="<?php echo get_permalink($event->ID) ?>"><img class="event-image" src="<?php echo $eventFields['thumbnail'] ?>" alt="event"></a>
        </div>
    <?php }
}

function events_library_shortcode() {
    $events = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => -1,
        'order'    => 'DESC'
    ]);?>
    <div class="event-search">
        <input type="text" placeholder="Search...">
    </div>
    <div class="library-container">
        <?php foreach ($events as $event) {
            $eventFields = get_fields($event);
            $eventTypes = get_the_category_list(',','',$event->ID );

            if(isAdmin()) {
                get_cards($eventFields, $event);
                continue;
            } elseif (str_contains(get_the_title( $event ), 'Calendar') || user_type() !== 'GOLD' && !str_contains($eventTypes, 'SILVER')) {
                continue;
            } else {
                get_cards($eventFields, $event);
            }
        }?>
   </div>
    <script>
        document.querySelector('.event-search input').addEventListener('input', e => {
            document.querySelectorAll('.event-card').forEach(el => {
                el.querySelector('p').innerText.toLowerCase().includes(e.target.value.toLowerCase()) ? el.classList.remove('hidden') : el.classList.add('hidden');
            })
        });
    </script>
<?php }

add_shortcode('events_library', 'events_library_shortcode');
/**
 * -------------------------------Hide upcoming, welcome, discount and calendar posts by id from Members page----------------------------------
 */
function wpb_exclude_from_everywhere($query) {
    if(isAdmin()) return;
    if ($query->is_home() || $query->is_feed() || $query->is_search() || $query->is_archive()) {
        $query->set('post__not_in', array(14657, 14769, 18021));
    }
}
add_action('pre_get_posts', 'wpb_exclude_from_everywhere');




