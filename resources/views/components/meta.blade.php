{{-- components.meta-preview --}}

<div class="permalink-metas" v-cloak inline-template>
    <div class="row">
        <div class="permalink-meta__tags col-md-6">
            <!-- Slug -->
            <div class="form-group">
                <label>Slug</label>
                <div class="input-group input-group-sm mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">{{ url }}</span>
                    </div>
                    <input type="text" class="form-control form-control-sm" name="page[slug]" v-model="slug">
                </div>
            </div>

            <!-- Page title -->
            <div class="form-group">
                <label class="d-flex">Page title
                    <span class="d-inline-block ml-auto font-weight-normal small">{{ title.length}}/60</span>
                </label>
                <input autocomplete="off" type="text" class="form-control form-control-sm" name="page[meta][title]"
                       v-model="title">
                <div class="text-danger font-weight-bold small mt-2" v-if="title.length > 60">
                    The title should not be longer than 60 characters.
                </div>

            </div>

            <!-- Meta description -->
            <div class="form-group">
                <label class="d-flex">Meta description
                    <span class="d-inline-block ml-auto font-weight-normal small">{{ description.length}}/300</span>
                </label>
                <textarea class="form-control form-control-sm" name="page[meta][description]" rows="3" v-model="description"></textarea>
                <div class="text-danger font-weight-bold small mt-2" v-if="description.length > 300">
                    The description should not be longer than 300 characters.
                </div>
            </div>
        </div>
        <div class="permalink-meta__preview col-md-6 border-left">
            <meta-preview></meta-preview>
        </div>
    </div>
</div>