# Snippets plugin for MantisBT

Copyright (c) 2010 - 2012  John Reese - http://noswap.com  
Copyright (c) 2012 - 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net

Released under the [MIT license](http://opensource.org/licenses/MIT)

See the [Changelog](https://github.com/mantisbt-plugins/snippets/blob/master/CHANGELOG.md).


## Description

Define snippets of text that can be easily pasted into text fields


## Requirements

The plugin requires [MantisBT](http://mantisbt.org/) version 2.3 or higher.

If you need compatibility with older releases of MantisBT, please use [legacy
versions](https://github.com/mantisbt-plugins/snippets/releases) of the plugin, 
as per table below:

| MantisBT version |                                       Plugin version                                        |
|:----------------:|:-------------------------------------------------------------------------------------------:|
|       1.3        | 1.x ([master-1.3.x branch](https://github.com/mantisbt-plugins/snippets/tree/master-1.3.x)) |
|       1.2        |            [0.6](https://github.com/mantisbt-plugins/snippets/releases/tag/v0.6)            |


## Installation

1. Download or clone a copy of the [plugin's code](https://github.com/mantisbt-plugins/snippets).
2. Copy the plugin (the `Snippets/` directory) into your Mantis
   installation's `plugins/` directory.
3. While logged into your Mantis installation as an administrator, go to
   *Manage -> Manage Plugins*.
4. In the *Available Plugins* list, you'll find the *Snippets* plugin;
   click the **Install** link.
5. In the *Installed Plugins* list, click on the **Snippets** plugin to configure it.


## Usage

### Managing Snippets

- Global snippets can be managed from *Manage -> Manage Plugins*.
- User-specific snippets can be managed from *My Account -> My Snippets*.

The following placeholders are supported in the Snippet's text; they will be
replaced by the corresponding contents when inserted:

| Placeholder  | Description             |
|:------------:|-------------------------|
|    {user}    | your username           |
|  {reporter}  | the bug reporter's name |
|  {handler}   | the bug handler's name  |
|  {project}   | the project name        |


### Using Snippets

Each configured text field will have a selection list above it, which can be
used to pick the desired Snippet.

Once selection is made, the Snippet's text will be inserted in the field at the
current position. If text is currently selected, the Snippet will replace the
selection.

By default only the *Bug Note* field is configured to use Snippets.
Other *text* fields (*Description*, *Steps To Reproduce* as well as *Additional
Information*) can be setup to use Snippets via configuration page `Manage > Global Snippets > Configuration`.

### REST API

The following public API endpoints can be used to manage Snippets.

Base URL is https://example.com/mantisbt/api/rest/plugins/Snippets

#### GET /search

Search for and return a list of Snippets available to the user.

Parameters:
- `query`: Return only Snippets having a title or contents matching the given
  search string. Default is no filtering.
- `limit`: Limit the number of Snippets returned. Default is 10.

#### GET /

Retrieve the list of Global or the user's Personal Snippets.

Parameters:
- `global`: 1 for global Snippets, 0 for personal Snippets. Default is 0.

#### POST /

Create a new snippet. Provide data as JSON body

```json
{
  "name": "Snippet's name",
  "text": "Snippet's body",
  "global": true / false
}
```

#### PUT /{SnippetId}

Update an existing Snippet.
Note that *global* state cannot be changed.

```json
{
  "name": "New name",
  "text": "New body"
}
```

#### DELETE /{SnippetId}

Delete snippet.


## Support

The following support channels are available if you wish to file a
[bug report](https://github.com/mantisbt-plugins/snippets/issues/new),
or have questions related to use and installation:

  - [GitHub issues tracker](http://github.com/mantisbt-plugins/snippets/issues)
  - MantisBT [Gitter chat room](https://gitter.im/mantisbt/mantisbt)
  - If you feel lucky you may also want to try the legacy
    [#mantisbt IRC channel](https://webchat.freenode.net/?channels=%23mantisbt)
    on Freenode (irc://freenode.net/mantisbt)
    but since hardly anyone goes there nowadays, you may not get any response.
