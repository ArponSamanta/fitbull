=== ArponSamanta Fitness Toolkit ===
Contributors: arponsamanta
Tags: fitness, workout, recipes, nutrition, gym
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Exercise library, workout plan generator, Indian recipes with a meal planner, macro and 1RM calculators, and a private progress log.

== Description ==

ArponSamanta Fitness Toolkit turns WordPress into a practical fitness site. It adds structured content types and interactive blocks that work with any block theme.

**Content types**

* Exercises, with level, movement pattern, step-by-step cues, common mistakes, muscles and equipment.
* Recipes, with calories and macros per serving, ingredients, method, diet (vegan, veg, eggetarian, non-veg) and meal.
* Gear, with budget tier, buying order, price and an optional buy link.
* Supplements, with an evidence rating and a plain-language verdict.

**Blocks**

* Workout plan generator: pick a goal, days per week, level and the equipment you own, and get a weekly plan built from your exercise library, with sets, reps, rest and one-click swaps.
* Meal planner: a day of meals close to a calorie and protein target, with portions adjusted automatically. It reads targets from the macro calculator.
* Macro calculator and one-rep-max calculator.
* Progress log: signed-in users log bodyweight and lifts, and see a trend chart and their best estimated maxes.
* Exercise library and recipe finder, with filters.
* Home gym kit builder, plus detail blocks for exercises, recipes and item facts.

**For developers**

Everything is exposed through a REST API under `/wp-json/fitbull/v1` (exercises, filters, recipes, workout-plan, meal-plan, macros, logs). The planning logic is plain PHP with unit tests. The React sources for the blocks are included in the `src/` folder, and `npm run build` rebuilds them.

**Demo content**

Go to Tools > Fitness Toolkit demo content, or run `wp fitness-toolkit seed`, to add 24 exercises, 16 Indian recipes, gear, supplements, sample articles, the tool pages and a menu.

== Installation ==

1. Install and activate the plugin from Plugins > Add New.
2. Optional: go to Tools > Fitness Toolkit demo content and click Import demo content.
3. Add the blocks to any page, or use the pages the importer creates.

== Frequently Asked Questions ==

= Does it need a particular theme? =

No. The blocks work with any block theme.

= Is the information medical advice? =

No. The calculators and plans give general estimates. Every tool says so, and you should add your own disclaimer page.

= Where are progress log entries stored? =

In a custom database table, `{prefix}fitbull_logs`, linked to the user's account. Only that user can read, add or delete their entries.

== Privacy ==

The progress log stores, for each signed-in user who uses it: the date, the entry type, an exercise ID, a weight and a rep count. Nothing is sent to any external service.

The plugin adds an exporter and an eraser to Tools > Export Personal Data and Tools > Erase Personal Data. Deleting a user also deletes their entries. Deleting the plugin removes the table.

The macro calculator remembers its inputs in the visitor's own browser (localStorage). They never reach the server.

== Screenshots ==

1. Homepage with the live macro calculator.
2. Workout plan generator.
3. Meal planner.
4. Recipe page with nutrition per serving.
5. Progress log.

== Changelog ==

= 1.0.2 =
* Renamed to ArponSamanta Fitness Toolkit (slug arponsamanta-fitness-toolkit).
* Security: block output is escaped where it is printed; video embeds are limited to an iframe allowlist.
* Security: detail blocks no longer read a post ID from the URL and never show drafts or private posts to visitors.

= 1.0.1 =
* Mobile: option buttons stack on narrow screens instead of breaking words.
* Mobile: gear kit cards keep the step number beside the title.
* Workout plan: fixed spacing in the equipment gap notice.

= 1.0.0 =
* First release.
