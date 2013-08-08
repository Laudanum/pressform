<!-- template gallery list -->
    <div class="gallery-container">
      <div class="gallery">
      <ul id="gallery-<?the_ID()?>">
        <?php
          $attachments = is_drawGallery();
          $total_attachments = count($attachments);
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

