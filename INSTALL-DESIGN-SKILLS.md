# نصب Design Skills — Cursor / Claude / Codex

خیلی کوتاه. یک‌بار کافی است.

## پیش‌نیاز

- Node.js نصب باشد
- ترمینال باز باشد

---

## نصب سریع (همه با هم)

در هر فولدری این‌ها را بزن:

```bash
# Taste Skill
npx skills add https://github.com/Leonxlnx/taste-skill -g -a cursor -a claude-code -a codex -s '*' -y

# Emil Kowalski (انیمیشن / UI)
npx skills add emilkowalski/skills -g -a cursor -a claude-code -a codex -s '*' -y

# GSAP
npx skills add https://github.com/greensock/gsap-skills -g -a cursor -a claude-code -a codex -s '*' -y

# HyperFrames (ویدیو — اختیاری)
npx skills add heygen-com/hyperframes --full-depth -g -a cursor -a claude-code -a codex -y

# UI UX Pro Max
npm install -g ui-ux-pro-max-cli --prefix "$HOME/.local"
export PATH="$HOME/.local/bin:$PATH"
uipro init --ai cursor --global
uipro init --ai claude --global
uipro init --ai codex --global

# Impeccable
npx impeccable install --providers=cursor,claude,codex --scope=global
```

بعد از نصب:

- **Cursor:** چت را ببند و دوباره باز کن  
- **Claude Code:** یک بار از ترمینال خارج شو و برگرد  
- **Codex:** همین  

---

## فقط یکی از ابزارها

| ابزار | فلگ agent |
|---|---|
| Cursor | `-a cursor` |
| Claude Code | `-a claude-code` |
| Codex | `-a codex` |

مثال فقط Claude:

```bash
npx skills add emilkowalski/skills -g -a claude-code -s '*' -y
```

---

## کجا نصب می‌شود؟

| ابزار | مسیر تقریبی |
|---|---|
| مشترک | `~/.agents/skills/` |
| Cursor | `~/.cursor/skills/` |
| Claude | `~/.claude/skills/` |
| Codex | `~/.agents/skills/` (و لینک‌ها) |

---

## چک کردن

```bash
npx skills list -g
```

---

## برای پروژه دلسا — بعد از نصب

در چت بگو:

1. `/impeccable init`
2. بعد: redesign صفحه اصلی با `design-taste-frontend` یا `redesign-existing-projects`
