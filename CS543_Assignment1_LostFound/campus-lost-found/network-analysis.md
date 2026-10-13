# Network analysis - browser DevTools -> Network

> **STATUS: TEMPLATE. No measurement below has been collected yet.**
> Replace every FILL marker (two less-than signs followed by the word FILL)
> with what you actually see in your browser.
> If something does not apply (for example no 3xx/4xx appeared), write that
> down explicitly instead of leaving it blank.

## What was analysed

| Item | Value |
|---|---|
| Website | <<FILL: any website, for example a college or news page>> |
| Page URL | <<FILL: the exact URL you loaded>> |
| Browser and version | <<FILL: Chrome/Edge -> menu -> Help -> About>> |
| Date and time | <<FILL>> |
| "Disable cache" ticked? | <<FILL: Yes - it must be Yes>> |
| Screenshot | `docs/screenshots/<<FILL: file name>>` |

## Method

1. Open the page in Chrome or Edge.
2. Press `F12` (or right-click -> Inspect) to open DevTools, then open the **Network** tab.
3. Tick **Disable cache** (at the top of the Network panel).
4. Reload the page with `Ctrl + R` and wait until loading finishes.
5. Read the waterfall and the summary bar at the bottom of the Network panel.

## Results

| Measurement | Value | Where I read it |
|---|---|---|
| Number of requests | <<FILL>> | Bottom bar: "`N` requests" |
| Total page size (transferred) | <<FILL: e.g. x.x MB>> | Bottom bar: "`x` MB transferred" |
| Total size of the resources (uncompressed) | <<FILL>> | Bottom bar: "`x` MB resources" |
| Page load time | <<FILL>> | Bottom bar: "Load" and "Finish" |

State clearly which size you treat as "total page size": <<FILL: transferred or resources>>.

## Single slowest resource

Click the **Time** column header to sort, then take the top row.

| Field | Value |
|---|---|
| Name / URL | <<FILL>> |
| Type (document, script, image, ...) | <<FILL: Type column>> |
| Time | <<FILL: Time column>> |
| Why it may be slow (one sentence, from what the waterfall shows) | <<FILL>> |

## 3xx and 4xx responses

Type `status-code:3` / `status-code:4` into the filter box, or sort by the
**Status** column, and list every 3xx and 4xx you see.

| Status | URL | What it means here |
|---|---|---|
| <<FILL>> | <<FILL>> | <<FILL>> |

If there were none, replace the table with: **"No 3xx or 4xx responses appeared
in this load."** (only if that is what you saw).

## Reading the waterfall (2-3 sentences)

<<FILL: in your own words, what loads first, what loads in parallel, and where
the long bars are.>>

## Before you submit

- [ ] "Disable cache" was ticked before the reload.
- [ ] Request count, total size, slowest resource and 3xx/4xx are all filled in from my browser.
- [ ] A screenshot is saved in `docs/screenshots/`.
- [ ] No FILL markers are left in this file.
