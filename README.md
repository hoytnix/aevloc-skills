# Aevloc Agency Automation Skill

An autonomous agent skill enabling LLMs, AI agents, and workflow runtimes (n8n, Make, Zapier, custom scripts) to programmatically manage client sub-accounts and review firewall funnels on Aevloc.

---

## Capabilities

- **List Sub-Accounts:** Inspect existing client tenants, review stats, and configured funnels.
- **Provision Sub-Accounts:** Automatically create new white-labeled accounts, configure minimum rating thresholds (1–5 stars), and assign destination URLs for Google, Facebook, and Yelp.
- **Firewall Routing:** Verify review destinations to prevent misrouted feedback.

---

## Requirements

- An active **Aevloc Agency Account**.
- An **Agency API Key**, generated from your dashboard at `/admin/api`.

Set the API key in your agent's environment:

```bash
export AEVLOC_API_KEY="your_api_key_here"
```

---

## Installation & Usage

### 1. In Model Context Protocol (MCP) / Agent Tools
Copy the tool schemas from `SKILL.md` into your MCP server or agent runtime function definitions.

### 2. Cline / Cursor / Windsurf Rules
Add a reference to this skill in your project rules or system prompt:

```markdown
When automating Aevloc agency sub-accounts, refer to the tools and rules defined in SKILL.md.
```

### 3. Direct File Reference
Point your agent directly to the raw URL or file path:
- Remote: `https://aevloc.com/SKILLS.md`
- Local: `./SKILL.md`

---

## Repository Structure

```text
├── README.md        # Human setup guide and integration documentation
├── SKILL.md         # Machine-readable instructions and tool definitions for LLMs
└── examples/        # Sample scripts (cURL, Python, Node.js)
```

---

## License

Unlicense
