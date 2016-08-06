<?php
get_header();
?>
<h1>HELLO</h1>
<?php
if(have_posts()): while(have_posts()): the_post();

endwhile; endif;
get_footer();
