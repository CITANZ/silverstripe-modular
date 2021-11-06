<% loop $ModularBlocks.Sort('SortOrder', 'ASC') %>
    <div
        class="
            col<% if $ColSize %>-$ColSize<% end_if %>
            <% if $ColSizeLg %>col-lg-$ColSizeLg<% end_if %>
            <% if $ColSizeMd %>col-md-$ColSizeMd<% end_if %>
            <% if $ColSizeSm %>col-sm-$ColSizeSm<% end_if %>
        "
    >
        $Me
    </div>
<% end_loop %>
