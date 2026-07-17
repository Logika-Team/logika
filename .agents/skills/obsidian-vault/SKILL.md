---
name: obsidian-vault
description: Search, create, and manage notes in the Obsidian vault with wikilinks and index notes. Use when user wants to find, create, or organize notes in Obsidian.
---

# Obsidian Vault

## Vault location

`/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/`

Project docs are stored in nested folders (not flat root):
- `/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/docs/guidelines`
- `/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/docs/changelog`
- `/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/rules`
- `/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/plugins/...`

## Naming conventions

- Preserve source folder structure (`docs/guidelines`, `docs/changelog`, `rules`, `plugins`, etc.) for easier tracing.
- Keep topic note names in **Title Case**.
- Prefer index notes for grouped topics and link-heavy overviews.
- For `docs/changelog`, keep one note per month with file name `YYYY-MM.md` and short daily sections (`## YYYY-MM-DD`) with bullet points.

## Linking

- Use Obsidian `[[wikilinks]]` syntax: `[[Note Title]]`
- Notes link to dependencies/related notes at the bottom
- Index notes are just lists of `[[wikilinks]]`

## Workflows

### Search for notes

```bash
# Search by filename
find "/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/" -name "*.md" | grep -i "keyword"

# Search by content
grep -rl "keyword" "/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/" --include="*.md"
```

Or use Grep/Glob tools directly on the vault path.

### Create a new note

1. Use **Title Case** for filename
2. Write content as a unit of learning (per vault rules)
3. Add `[[wikilinks]]` to related notes at the bottom
4. If part of a numbered sequence, use the hierarchical numbering scheme

### Find related notes

Search for `[[Note Title]]` across the vault to find backlinks:

```bash
grep -rl "\\[\\[Note Title\\]\\]" "/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/"
```

### Find index notes

```bash
find "/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school/" -name "*Index*"
```
