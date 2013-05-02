function Template() {
    
    this.tabs = new Array(
        '<div class="tabbable">',
            '<ul class="nav nav-tabs">{{#tabs}}',
                '<li><a data-toggle="tab" href="#{{id}}">{{name}}</a></li>',
            '{{/tabs}}</ul>',
            '<div class="tab-content">{{#tabs}}',
                '<div class="tab-pane" id="{{id}}">{{&content}}</div>',
            '{{/tabs}}</div>',
        '</div>').join('')
    
    this.query_form =
            '<form action="{{url}}">\
            {{#fields}}<label>{{.}}</label><input name="{{.}}" type="text"/>{{/fields}}\
            <button class="btn btn-primary">Send</button>\
            </form>\
            '
}