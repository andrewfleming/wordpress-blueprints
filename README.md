# wordpress-blueprints

A personal collection of [WordPress Playground](https://wordpress.github.io/wordpress-playground/) / [Studio](https://developer.wordpress.com/studio/) blueprints.

Each blueprint lives in its own folder under `blueprints/`, with `blueprint.json` at a fixed path so tooling (and CI) can glob `blueprints/*/blueprint.json`. Plugin/theme source hosted in this repo is referenced directly via `git:directory` resources — no need to zip anything. Shared, cross-blueprint snippets and content live under `shared/`.

## Blueprints

| Blueprint | Description | |
|---|---|---|
| [ai-building-blocks](blueprints/ai-building-blocks/) | Stub — TODO | [![Open in Studio](https://developer.wordpress.com/wp-content/uploads/2024/09/open-in-studio-button.svg)](https://wp.com/open?deep_link=add-site%3Fblueprint%3Dhttps%3A%2F%2Fraw.githubusercontent.com%2Fandrewfleming%2Fwordpress-blueprints%2Fmain%2Fblueprints%2Fai-building-blocks%2Fblueprint.json) |

> Raw URLs above assume this repo is pushed to `github.com/andrewfleming/wordpress-blueprints` on the `main` branch. Update the owner/repo/branch in the links once the remote is created if these differ.

## Testing locally

```bash
npx @wp-playground/cli server --blueprint=./blueprints/<name>/blueprint.json
```

## CI

`.github/workflows/validate-blueprints.yml` runs a headless `run-blueprint` over every folder under `blueprints/*` on push/PR to catch schema errors before merge.
