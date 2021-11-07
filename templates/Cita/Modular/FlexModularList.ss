<% loop $ModularBlocks.Sort('SortOrder', 'ASC') %>
    <div
        class="
            col<% if $ColSize %>-$ColSize<% end_if %>
            <% if $ColSizeLg %>col-lg-$ColSizeLg<% end_if %>
            <% if $ColSizeMd %>col-md-$ColSizeMd<% end_if %>
            <% if $ColSizeSm %>col-sm-$ColSizeSm<% end_if %>
            <% if $ColOffset %>offset-$ColOffset<% end_if %>
            <% if $ColOffsetSm %>offset-sm-$ColOffsetSm<% end_if %>
            <% if $ColOffsetMd %>offset-md-$ColOffsetMd<% end_if %>
            <% if $ColOffsetLg %>offset-lg-$ColOffsetLg<% end_if %>
        "
    >
        $Renderer(3)
    </div>
<% end_loop %>
