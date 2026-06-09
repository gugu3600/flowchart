<script setup>
</script>

<template>
  <div class="help-page">
    <header class="help-header">
      <h1 class="help-title">Flowchart — How to Use</h1>
      <a href="/canvas" class="help-back">Back to Canvas</a>
    </header>

    <main class="help-content">
      <section>
        <h2>What is Flowchart?</h2>
        <p>
          Flowchart is a visual diagramming tool for designing architecture, workflows, and database schemas.
          Drag nodes onto a canvas, connect them with edges, and save your diagrams as reusable flows.
        </p>
      </section>

      <section>
        <h2>Authentication</h2>
        <p>Visit <a href="/login">/login</a> or <a href="/register">/register</a> to sign in or create an account.</p>
        <p>Demo credentials: <code>admin@flowchart.dev</code> / <code>password</code></p>
      </section>

      <section>
        <h2>Canvas — Two Modes</h2>
        <p>The canvas (<a href="/canvas">/canvas</a>) has two tabs at the top:</p>
        <ul>
          <li><strong>Flow</strong> — for workflow and architecture diagrams using Logic and Folder/File nodes.</li>
          <li><strong>Schema</strong> — for database relationship diagrams using Table nodes.</li>
        </ul>
        <p>Nodes from different modes <strong>cannot</strong> connect to each other — this is enforced by the canvas.</p>
      </section>

      <section>
        <h2>Using the Canvas</h2>

        <h3>Creating Nodes</h3>
        <p>Drag a node type from the left sidebar onto the canvas. In Flow mode you will see Logic and Folder/File types; in Schema mode you will see Table types.</p>

        <h3>Connecting Nodes</h3>
        <p>Click and drag from a <strong>handle</strong> (small circle on a node's edge) to a handle on another node. An animated edge appears between them.</p>

        <h3>Deleting Edges &amp; Nodes</h3>
        <p>Click an edge or node to select it (it will highlight), then press <kbd>Delete</kbd> or <kbd>Backspace</kbd> on your keyboard.</p>

        <h3>Managing Flows</h3>
        <ul>
          <li><strong>Select a flow</strong> — use the dropdown in the header to switch between saved flows.</li>
          <li><strong>Create a flow</strong> — click <em>+ New Flow</em>, enter a name, and press Enter.</li>
          <li><strong>Save</strong> — click the <em>Save</em> button to persist all nodes and edges to the server.</li>
          <li><strong>Refresh</strong> — click the refresh button to reload definitions from the server.</li>
        </ul>
      </section>

      <section>
        <h2>Table Designer</h2>
        <p>Visit <a href="/tables">/tables</a> to create database table definitions. Each table has:</p>
        <ul>
          <li>A <strong>name</strong></li>
          <li>One or more <strong>columns</strong> with name, type, and optional PK / FK / UQ flags</li>
        </ul>
        <p>Saved table definitions automatically appear as nodes when you open the canvas in Schema mode.</p>
      </section>

      <section>
        <h2>Logic Designer</h2>
        <p>Visit <a href="/logics">/logics</a> to create logic/function definitions. Each logic has:</p>
        <ul>
          <li>A <strong>name</strong> and optional <strong>description</strong></li>
          <li>One or more <strong>inputs</strong> with name and type</li>
          <li>An optional <strong>output</strong> type</li>
        </ul>
        <p>Saved logic definitions automatically appear as nodes when you open the canvas in Flow mode.</p>
      </section>

      <section>
        <h2>Keyboard Shortcuts</h2>
        <table class="shortcuts">
          <thead>
            <tr><th>Key</th><th>Action</th></tr>
          </thead>
          <tbody>
            <tr><td><kbd>Delete</kbd> / <kbd>Backspace</kbd></td><td>Remove selected edge(s) or node(s)</td></tr>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</template>

<style scoped>
.help-page {
  min-height: 100vh;
  background: #0f172a;
  color: #e2e8f0;
}

.help-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background: #1e293b;
  border-bottom: 1px solid #334155;
}

.help-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #f1f5f9;
}

.help-back {
  font-size: 0.875rem;
  color: #60a5fa;
  text-decoration: none;
}

.help-back:hover {
  text-decoration: underline;
}

.help-content {
  max-width: 720px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
}

section {
  margin-bottom: 2.5rem;
}

h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #f1f5f9;
  margin: 0 0 0.75rem;
}

h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #cbd5e1;
  margin: 1rem 0 0.5rem;
}

p {
  line-height: 1.6;
  margin: 0 0 0.5rem;
}

ul {
  padding-left: 1.25rem;
}

li {
  margin-bottom: 0.35rem;
  line-height: 1.5;
}

a {
  color: #60a5fa;
}

code {
  background: #1e293b;
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
  font-size: 0.85em;
  color: #f472b6;
}

kbd {
  background: #334155;
  border: 1px solid #475569;
  border-radius: 4px;
  padding: 0.1rem 0.35rem;
  font-size: 0.85em;
  font-family: inherit;
  color: #f1f5f9;
}

.shortcuts {
  width: 100%;
  border-collapse: collapse;
}

.shortcuts th,
.shortcuts td {
  text-align: left;
  padding: 0.5rem 0.75rem;
  border-bottom: 1px solid #334155;
}

.shortcuts th {
  color: #94a3b8;
  font-weight: 500;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
</style>
