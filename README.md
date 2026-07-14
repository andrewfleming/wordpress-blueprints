# wordpress-blueprints

A personal collection of [WordPress Playground](https://wordpress.github.io/wordpress-playground/) / [Studio](https://developer.wordpress.com/studio/) blueprints.

Each blueprint lives in its own folder under `blueprints/`, with `blueprint.json` at a fixed path so tooling (and CI) can glob `blueprints/*/blueprint.json`. Plugin/theme source hosted in this repo is referenced directly via `git:directory` resources — no need to zip anything. Shared, cross-blueprint snippets and content live under `shared/`.

## Blueprints

| Blueprint | Description | |
|---|---|---|
| [ai-building-blocks](blueprints/ai-building-blocks/) | Stub — TODO | [![Open in Studio](https://developer.wordpress.com/wp-content/uploads/2024/09/open-in-studio-button.svg)](https://wp.com/open?deep_link=add-site%3Fblueprint%3DeyIkc2NoZW1hIjoiaHR0cHM6Ly9wbGF5Z3JvdW5kLndvcmRwcmVzcy5uZXQvYmx1ZXByaW50LXNjaGVtYS5qc29uIiwibWV0YSI6eyJ0aXRsZSI6IkFJIEJ1aWxkaW5nIEJsb2NrcyIsImF1dGhvciI6ImFqZmxlbWluZyIsImRlc2NyaXB0aW9uIjoiU3R1YiBibHVlcHJpbnQgXHUyMDE0IFRPRE86IGZsZXNoIG91dCB3aXRoIHRoZSBhY3R1YWwgcGx1Z2lucy90aGVtZS9jb250ZW50IGZvciB0aGUgQUkgYnVpbGRpbmcgYmxvY2tzIGRlbW8uIn0sImxhbmRpbmdQYWdlIjoiL3dwLWFkbWluLyIsInByZWZlcnJlZFZlcnNpb25zIjp7InBocCI6IjguMyIsIndwIjoibGF0ZXN0In0sImxvZ2luIjp0cnVlLCJzdGVwcyI6W3sic3RlcCI6Imluc3RhbGxQbHVnaW4iLCJwbHVnaW5EYXRhIjp7InJlc291cmNlIjoid29yZHByZXNzLm9yZy9wbHVnaW5zIiwic2x1ZyI6Imd1dGVuYmVyZyJ9LCJvcHRpb25zIjp7ImFjdGl2YXRlIjp0cnVlfX0seyJzdGVwIjoiaW1wb3J0V3hyIiwiZmlsZSI6eyJyZXNvdXJjZSI6InVybCIsInVybCI6Imh0dHBzOi8vcmF3LmdpdGh1YnVzZXJjb250ZW50LmNvbS9hbmRyZXdmbGVtaW5nL3dvcmRwcmVzcy1ibHVlcHJpbnRzL21haW4vc2hhcmVkL2NvbnRlbnQvYWktYnVpbGRpbmctYmxvY2tzK2d1aWRlbGluZXMueG1sIn19XX0%3D) |

> The Open-in-Studio button embeds each blueprint as base64 JSON directly in the deep link, rather than pointing at its raw GitHub URL. Studio's deep-link handler currently fails to gunzip the response when fetching a remote blueprint URL (`raw.githubusercontent.com` serves gzip-encoded content), which breaks the `https://wp.com/open?...&blueprint=<url>` form with a `JSON.parse` error. Embedding the JSON sidesteps that fetch entirely. **This means the button link must be regenerated whenever a blueprint.json changes** — minify the JSON, base64-encode it, URL-encode `add-site?blueprint=<base64>`, and prefix with `https://wp.com/open?deep_link=`.

## Testing locally

```bash
npx @wp-playground/cli server --blueprint=./blueprints/<name>/blueprint.json
```

## CI

`.github/workflows/validate-blueprints.yml` runs a headless `run-blueprint` over every folder under `blueprints/*` on push/PR to catch schema errors before merge.
