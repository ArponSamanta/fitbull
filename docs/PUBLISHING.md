# Publishing Fitbull (free)

Three free ways to put Fitbull in front of people. Do them in this order.

## 1. Live demo on WordPress Playground (5 minutes)

Playground runs WordPress in the visitor's browser, so there is no hosting to pay for.

1. Push this repository to GitHub as a **public** repo named `fitbull` under `ArponSamanta`.
2. Open the demo link:
   `https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/ArponSamanta/fitbull/main/blueprint.json`
3. Check that the homepage, `/workout-plan/`, `/meal-plan/` and `/my-progress/` load. The blueprint logs the visitor in as admin, so the progress log works too.

If you rename the repo or account, update both URLs in `blueprint.json` and the README.

Note: Playground demos reset when the tab closes. That is fine for a portfolio link.

## 2. Release zips (2 minutes)

```bash
git tag v1.0.0
git push origin v1.0.0
```

The `Release zips` workflow builds `fitbull-core.zip` (plugin) and `fitbull.zip` (theme) and attaches them to a GitHub release. You can also build them locally with `bin/package.sh` (they land in `dist/`).

## 3. WordPress.org directories

You need a free WordPress.org account. It is separate from your WordPress.com account. Before you submit, replace `arponsamanta` in both `readme.txt` files with your WordPress.org username (the `Contributors:` line).

Both packages already pass the official checkers: **Plugin Check** reports no errors, and **Theme Check** reports only an informational note.

### Plugin: Fitbull Core

1. Add plugins@wordpress.org to your email allowlist, so review emails don't go to spam.
2. Go to the [plugin submission page](https://wordpress.org/plugins/developers/add/) and upload `fitbull-core.zip`.
3. The handbook says reviews happen within 14 business days of being queued. Reply quickly to any email from the review team.
4. Once approved, you get SVN access. Check out the repo and copy in the plugin (the same files as the zip):
   ```bash
   svn co https://plugins.svn.wordpress.org/fitbull-core fitbull-core-svn
   cd fitbull-core-svn
   unzip ../dist/fitbull-core.zip -d /tmp/fb && cp -R /tmp/fb/fitbull-core/* trunk/
   cp ../.wordpress-org/screenshot-*.png assets/
   svn add --force trunk assets
   svn cp trunk tags/1.0.0
   svn ci -m "Release 1.0.0"
   ```
5. The plugin page appears a few minutes later. The slug (`fitbull-core`) can't be changed after you submit, so be sure of it first.

### Theme: Fitbull

1. Go to the [theme upload page](https://wordpress.org/themes/upload/) and upload `fitbull.zip`.
2. It enters the review queue. No timeframe is promised; it depends on how many reviewers are active.
3. Watch the ticket. Reply within 7 days, or the ticket may be closed.
4. Only one theme can be in the queue at a time.

Theme reviewers check that the theme works **without** the plugin. That's why the plugin-only sections (hubs, exercise finder, call-to-action band, the calculator in the hero) hide themselves when Fitbull Core is inactive, and why the header menu and footer have no hard-coded links.

## Optional: a permanent free site

Playground is enough for a portfolio. If you want a site that keeps its data, free PHP hosts exist. They are usually slow, show ads or have limits, so read their terms before you rely on one. Install WordPress there, upload the two zips, then use Tools > Fitbull demo content.

## Sources

- [Planning, Submitting, and Maintaining Plugins](https://developer.wordpress.org/plugins/wordpress-org/planning-submitting-and-maintaining-plugins/)
- [Theme Review Process](https://make.wordpress.org/themes/handbook/review/)
- [Plugin submission page](https://wordpress.org/plugins/developers/add/)
