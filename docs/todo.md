# TODO
This file is used to keep track of things that we intend on doing, and all accepted feature requests. `TODO` denotes an item that we intend to work on. `FREQ` indicates a feature request.

- TODO: Remove abstraction from templates, Smarty only. Consider whether we'd want Twig instead, however
- TODO: Add isset & filter_input combo to *all* inputs
- TODO: Scheduler should have a check to ensure that each player does not have more presets (total/count) than max_presets
- TODO: Sector fighter should be redone so that it is class driven, but its going to take extensive testing to do so
- TODO: CU, admin, and scheduler should all be class-driven, and should have a database entry for active/inactive on each sub-item. This will also eliminate the need for direct access checks.
- TODO: ship.php could have a better error message for handling when you navigate to it directly (instead of from main, when a ship has been detected)
- TODO: Audit all SQL calls to ensure they use row & value style calls, and also use a debug call
- TODO: Add encryption on submission for admin panel and login
- TODO: Redo scheduler to be a subdirectory (like admin) with activated scheduler events
- TODO: Language translations for all elements in scheduler
- TODO: Merge mail.php and mailto.php
- TODO: Cleanup functions and calls in scheduler
- TODO: Eliminate $color_line variables
- TODO: Convert main to be template driven
- TODO: Finish moving Sol from sector 0 to sector 1
- TODO: Implement Post->Redirect->Get pattern for the 60 post calls in the game
- TODO: Eliminate all reg_globals equivalent hacks
- FREQ: Migrate all files to use templates
- TODO: Postgresql compatibility
- TODO: Split ships table from players table
- TODO: Review schema for improvements (BIGINT, more indexes, reduce unstructured data, etc)
- TODO: Add new variable for admin to set that provides https independent source for images/css/js/etc. Basically supporting a CDN. Variable can be handled much like template is.
