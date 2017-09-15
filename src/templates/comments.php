<div id="comments">
<?php
$project_id = 50;
$item_id = "159b2a0141a7de";


$rowset = get_comments(array(
    'post_id'    => $project_id,
    //'meta_key'   => "item_id",
    //'meta_value' => $item_id,
    'order' => 'ASC'
));

function formatCommentData($data)
{
  $comment = (object)array(
    'id'         => (int)$data->comment_ID,
    'parent_id'  => (int)$data->comment_parent,
    'created_by' => (string)$data->comment_author,
    'content'    => (string)$data->comment_content,
    'created_at' => strtotime($data->comment_date_gmt),
    'children'   => array()
  );

  return $comment;
}

function appendChildToParent($comment, &$map)
{
    if (isset($map[$comment->parent_id])) {
        $map[$comment->parent_id]->children[$comment->id] = $comment;

        return true;
    }

    if (count($map) === 0) {
        return false;
    }

    foreach ($map as &$mapNode) {
        if (appendChildToParent($comment, $mapNode->children)) {
            return true;
        }
    }

    return false;
}

function createMap($rowset)
{
    $comments = array();
    $children = array();

    foreach ($rowset as $rowIndex => $row) {
        $comment = formatCommentData($row);

        if ($comment->parent_id <= 0) {
            $comments[$comment->id] = $comment;
        } else {
            array_push($children, $comment);
        }

        $rowset[$rowIndex] = $comment;
    }

    if (count($children) > 0) {
        foreach ($children as $child) {
            appendChildToParent($child, $comments);
        }
    }

    return $comments;
}

$maxCommentsDepthAllowed = (int)get_option('thread_comments_depth');
function renderCommentHtml($data, $parent = null, $currentDepth = 1, $maxCommentsDepthAllowed)
{
    ?>
    <div class="media o-comment" id="comment-<?php echo md5($data->id); ?>">
      <div class="media-left">
        <img class="media-object img-rounded" src="//randomuser.me/api/portraits/men/<?php echo mt_rand(1, 99); ?>.jpg" width="32" />
      </div>
      <div class="media-body">
        <div class="media-heading o-comment__header">
          <div class="o-comment__header__user">
            <strong><?php echo $data->created_by; ?></strong>
          </div>
          <?php if ($parent !== null): ?>
          &nbsp;
          <a href="#comment-<?php echo md5($parent->id); ?>">
            <i class="fa fa-reply fa-flip-horizontal" style="font-size: .8em;"></i>&nbsp;<?php echo $parent->created_by; ?>
          </a>
          <?php endif; ?>
          &nbsp;<span style="color: #ecf0f1;">•</span>&nbsp;
          <time><?php echo date('F j, Y', $data->created_at); ?>&nbsp;<span style="color: #ecf0f1;">•</span>&nbsp;<?php echo date('H:i', $data->created_at); ?></time>
        </div>
        <div><?php echo $data->content; ?></div>
        <ul class="list-inline o-comment__toolbar">
          <li>
            <a href="#" data-action="comment:edit">Edit</a>
          </li>
          <li>
            <a href="#" data-action="comment:reply">Reply</a>
          </li>
          <li>
            <a href="#" data-action="comment:delete">Delete</a>
          </li>
        </ul>
        <?php if ($currentDepth <= $maxCommentsDepthAllowed): ?>
          <?php if (count($data->children) > 0): ?>
            <?php $childrenDepth = $currentDepth + 1; ?>
            <?php $data->children = array_reverse($data->children); ?>
            <?php foreach ($data->children as $child): ?>
            <?php renderCommentHtml($child, $data, $childrenDepth, $maxCommentsDepthAllowed); ?>
            <?php endforeach; ?>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php if ($currentDepth > $maxCommentsDepthAllowed): ?>
      <?php if (count($data->children) > 0): ?>
        <?php $childrenDepth = $currentDepth + 1; ?>
        <?php $data->children = array_reverse($data->children); ?>
        <?php foreach ($data->children as $child): ?>
        <?php renderCommentHtml($child, $data, $childrenDepth, $maxCommentsDepthAllowed); ?>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>
    <?php
}


$commentsTree = array_reverse(createMap($rowset));
?>
    <?php foreach ($commentsTree as $row): ?>
    <?php renderCommentHtml($row, null, 1, $maxCommentsDepthAllowed); ?>
    <?php endforeach; ?>

<?php //die(); ?>
