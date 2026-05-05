# Commit Conventions

## Format

```
<prefix>(<scope>): <short message>
```

## Rules

- Message must not exceed **12 words**
- Always include a **prefix** matching the change type
- No `Co-Authored-By` or any signature trailer
- Write in **English**

## Prefixes

| Prefix | Use when |
|---|---|
| `feat` | New feature or functionality |
| `fix` | Bug fix |
| `chore` | Tooling, config, dependencies |
| `docs` | Documentation only |
| `style` | Formatting, no logic change |
| `refactor` | Code restructure, no behavior change |
| `perf` | Performance improvement |
| `test` | Adding or updating tests |

## Examples

```
feat(blog): add 6 SEO posts for Q1 2026
fix(home): rewrite slider with transform-based navigation
chore(deps): update next to 15.3
docs(commits): add commit conventions guide
refactor(admin): split skills CRUD into smaller components
```

## Anti-patterns

```
# Too long (over 12 words)
feat(home): replace overflow-x scroll approach with CSS transform-based carousel navigation system

# Missing prefix
update slider navigation

# Has signature
fix(blog): correct post date format

Co-Authored-By: Claude <noreply@anthropic.com>
```
