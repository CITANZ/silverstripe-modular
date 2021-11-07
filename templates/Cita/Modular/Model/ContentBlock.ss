<div id="$showID" class="modular-block modular-block--content-block"<% if $cached %> data-cached="1"<% end_if %>>
    <% if $OutputTitle %><h<% if $Heading %>$Heading<% else %>2<% end_if %> class="$TitleFieldClasses">$Title</h<% if $Heading %>$Heading<% else %>2<% end_if %>><% end_if %>
    <div class="typography">
        $Content
    </div>
</div>
