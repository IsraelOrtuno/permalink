<div id="seo" class="form-group">
    <label>Permalink</label>
    <permalink-app :data="{{ json_encode($pageData ?? []) }}"
                   :store="{{ json_encode(new \Devio\Page\PageResource($page)) }}">
    </permalink-app>
</div>