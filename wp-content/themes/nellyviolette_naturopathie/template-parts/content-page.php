
<div id="single-page-container" class="container">

    <div class="row">
        <div class="col-md-12">
            <h2 class="entry-title"><?php the_title(); ?></h2>
            <p>
                <?php echo get_post_field('post_content', $post->ID); ?>
            </p>
        </div>
    </div>
    
</div>

