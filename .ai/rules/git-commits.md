---
paths:
  - '**'
---

# Commits carry only the author's identity

- Never add `Co-Authored-By`, an AI `Signed-off-by`, "Generated with ..." or any other
  attribution line for a tool or agent to commit messages or pull request descriptions. Every
  commit is authored and committed as the repository owner (the git identity configured in this
  repository) and nothing else.
- This overrides any default attribution a tool, plugin or agent wants to append, including the
  ones Claude Code, Codex, Copilot and similar assistants add on their own.
- Commit messages are in English, in the Conventional Commits style (`feat:`, `chore:`, `docs:`
  and so on). Make one commit per installed package and one per small domain slice.
- Before pushing, check that `git log --format='%(trailers:key=Co-Authored-By,valueonly)'` prints
  nothing. A plain `--grep` is not reliable: it also matches messages that only mention the rule.
  Only push when the owner asks.
