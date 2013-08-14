<!-- template gallery list -->
    <div class="gallery-container">
      <div class="gallery">
      <ul id="gallery-<?the_ID()?>">
        <?php
          $attachments = is_getAttachments();
          is_drawImageGallery();
          $total_attachments = count($attachments);

          // videos [youtube=http://www.youtube.com/watch?v=JaNH56Vpg-A]
          // shortcode processor
          foreach ( $attachments['video'] as $v ) {
            echo '<li class="gallery-node type-video">';
            if ( shortcode_exists( 'embed' ) ) {
              global $wp_embed;
              $code = '[embed width="450" height="320"]' . $v->src . '[/embed]';
              echo $wp_embed->run_shortcode($code);
            } else {
              echo $v->src;
            }
            echo '</li>';
          }

        ?>
      </ul>
      </div>
      <div class="gallery-navigation">
        <nav class="clearfix <? if ( $total_attachments < 2 ) echo 'one-attachment'; ?>">
          <a class="previous" href="#previous">Previous</a>
          <a class="next active" href="#next">Next</a>
          <span class="counter">1 of <?=$total_attachments?></span>
        </nav>
      </div>
    </div>

