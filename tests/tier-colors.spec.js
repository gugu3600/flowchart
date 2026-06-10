import { test, expect } from '@playwright/test'

const PASSWORD = 'TestPass1!'

async function register(request, tier, label) {
  const email = `${tier}-${label}-${Date.now()}@test.dev`
  const regRes = await request.post('http://localhost:8000/api/register', {
    data: {
      name: `${tier} ${label}`,
      email,
      password: PASSWORD,
      password_confirmation: PASSWORD,
      tier,
      payment_method: 'kbzpay',
    },
  })
  expect(regRes.ok()).toBeTruthy()
  return email
}

async function createFlow(request, name) {
  const flowRes = await request.post('http://localhost:8000/api/flows', {
    data: { name },
  })
  expect(flowRes.ok()).toBeTruthy()
  const flowData = await flowRes.json()
  return flowData.data.flow.id
}

async function saveFlowWithNodeColor(request, flowId, color) {
  const saveRes = await request.post(`http://localhost:8000/api/flows/${flowId}/save`, {
    data: {
      nodes: [
        {
          id: 'node_1',
          type: 'logic',
          label: 'Color Node',
          position_x: 100,
          position_y: 200,
          data: { label: 'Color Node' },
          config: { backgroundColor: color },
        },
      ],
      edges: [],
    },
  })
  expect(saveRes.ok()).toBeTruthy()
  const saveData = await saveRes.json()
  expect(saveData.data.flow.nodes[0].config.backgroundColor).toBe(color)
}

async function saveFlowWithEdgeColor(request, flowId, color) {
  const saveRes = await request.post(`http://localhost:8000/api/flows/${flowId}/save`, {
    data: {
      nodes: [
        { id: 'node_a', type: 'logic', label: 'Node A', position_x: 100, position_y: 200, data: { label: 'Node A' }, config: {} },
        { id: 'node_b', type: 'logic', label: 'Node B', position_x: 400, position_y: 200, data: { label: 'Node B' }, config: {} },
      ],
      edges: [
        { source: 'node_a', target: 'node_b', config: { strokeColor: color } },
      ],
    },
  })
  if (!saveRes.ok()) {
    const errText = await saveRes.text()
    throw new Error(`Save failed (${saveRes.status()}): ${errText}`)
  }
  const saveData = await saveRes.json()
  expect(saveData.data.flow.edges).toHaveLength(1)
  expect(saveData.data.flow.edges[0].config.strokeColor).toBe(color)
}

async function createTable(request, name) {
  const tableRes = await request.post('http://localhost:8000/api/tables', {
    data: {
      name,
      columns: [
        { name: 'id', type: 'BIGINT', pk: true },
        { name: 'title', type: 'VARCHAR(255)' },
      ],
    },
  })
  return tableRes
}

test.describe('Color customization tier enforcement', () => {
  test('free tier cannot create flows (no save-flows)', async ({ request }) => {
    await register(request, 'free', 'API-Color')

    const flowRes = await request.post('http://localhost:8000/api/flows', {
      data: { name: 'Free Color Flow' },
    })
    expect(flowRes.ok()).toBeFalsy()
  })

  test('silver tier can save a node with backgroundColor config and read it back', async ({ request }) => {
    await register(request, 'silver', 'API-Color')
    const flowId = await createFlow(request, 'Silver Color Flow')

    const saveRes = await request.post(`http://localhost:8000/api/flows/${flowId}/save`, {
      data: {
        nodes: [
          {
            id: 'node_1',
            type: 'logic',
            label: 'Color Node',
            position_x: 100,
            position_y: 200,
            data: { label: 'Color Node' },
            config: { backgroundColor: '#3b82f6' },
          },
        ],
        edges: [],
      },
    })
    expect(saveRes.ok()).toBeTruthy()
    const saveData = await saveRes.json()
    const savedNode = saveData.data.flow.nodes[0]
    expect(savedNode.config.backgroundColor).toBe('#3b82f6')
  })

  test('silver tier can save an edge with strokeColor config', async ({ request }) => {
    await register(request, 'silver', 'Edge-Color')
    const flowId = await createFlow(request, 'Silver Edge Flow')

    const saveRes = await request.post(`http://localhost:8000/api/flows/${flowId}/save`, {
      data: {
        nodes: [
          { id: 'node_a', type: 'logic', label: 'Node A', position_x: 100, position_y: 200, data: { label: 'Node A' }, config: {} },
          { id: 'node_b', type: 'logic', label: 'Node B', position_x: 400, position_y: 200, data: { label: 'Node B' }, config: {} },
        ],
        edges: [
          { source: 'node_a', target: 'node_b', config: { strokeColor: '#ef4444' } },
        ],
      },
    })
    if (!saveRes.ok()) {
      const errText = await saveRes.text()
      throw new Error(`Save failed (${saveRes.status()}): ${errText}`)
    }
    const saveData = await saveRes.json()
    expect(saveData.data.flow.edges).toHaveLength(1)
    const savedEdge = saveData.data.flow.edges[0]
    expect(savedEdge.config.strokeColor).toBe('#ef4444')
  })

  test('gold tier can save node backgroundColor', async ({ request }) => {
    await register(request, 'gold', 'Node-Color')
    const flowId = await createFlow(request, 'Gold Color Flow')
    await saveFlowWithNodeColor(request, flowId, '#10b981')
  })

  test('gold tier can save edge strokeColor', async ({ request }) => {
    await register(request, 'gold', 'Edge-Color')
    const flowId = await createFlow(request, 'Gold Edge Flow')
    await saveFlowWithEdgeColor(request, flowId, '#f59e0b')
  })

  test('gold tier can create table definitions (generate-schema)', async ({ request }) => {
    await register(request, 'gold', 'Table-CRUD')

    const createRes = await createTable(request, 'GoldTable')
    expect(createRes.ok()).toBeTruthy()
    const createData = await createRes.json()
    const tableId = createData.data.table.id

    const readRes = await request.get(`http://localhost:8000/api/tables/${tableId}`)
    expect(readRes.ok()).toBeTruthy()
    const readData = await readRes.json()
    expect(readData.data.table.name).toBe('GoldTable')

    const updateRes = await request.put(`http://localhost:8000/api/tables/${tableId}`, {
      data: { name: 'GoldTableUpdated', columns: [{ name: 'id', type: 'BIGINT', pk: true }] },
    })
    expect(updateRes.ok()).toBeTruthy()
    const updateData = await updateRes.json()
    expect(updateData.data.table.name).toBe('GoldTableUpdated')

    const deleteRes = await request.delete(`http://localhost:8000/api/tables/${tableId}`)
    expect(deleteRes.ok()).toBeTruthy()
  })

  test('gold tier can create unlimited logics', async ({ request }) => {
    await register(request, 'gold', 'Unlimited-Logics')

    for (let i = 0; i < 6; i++) {
      const res = await request.post('http://localhost:8000/api/logics', {
        data: {
          name: `GoldLogic${i}`,
          description: 'test',
          inputs: [{ name: 'x', type: 'string' }],
          output: 'void',
        },
      })
      expect(res.ok()).toBeTruthy()
    }
  })

  test('platinum tier can save node backgroundColor', async ({ request }) => {
    await register(request, 'platinum', 'Node-Color')
    const flowId = await createFlow(request, 'Platinum Color Flow')
    await saveFlowWithNodeColor(request, flowId, '#8b5cf6')
  })

  test('platinum tier can save edge strokeColor', async ({ request }) => {
    await register(request, 'platinum', 'Edge-Color')
    const flowId = await createFlow(request, 'Platinum Edge Flow')
    await saveFlowWithEdgeColor(request, flowId, '#ec4899')
  })

  test('platinum tier can create table definitions (generate-schema)', async ({ request }) => {
    await register(request, 'platinum', 'Table-CRUD')

    const createRes = await createTable(request, 'PlatinumTable')
    expect(createRes.ok()).toBeTruthy()
    const createData = await createRes.json()
    const tableId = createData.data.table.id

    const readRes = await request.get(`http://localhost:8000/api/tables/${tableId}`)
    expect(readRes.ok()).toBeTruthy()

    const updateRes = await request.put(`http://localhost:8000/api/tables/${tableId}`, {
      data: { name: 'PlatinumTableUpdated', columns: [{ name: 'id', type: 'BIGINT', pk: true }] },
    })
    expect(updateRes.ok()).toBeTruthy()

    const deleteRes = await request.delete(`http://localhost:8000/api/tables/${tableId}`)
    expect(deleteRes.ok()).toBeTruthy()
  })

  test('platinum tier can create unlimited logics', async ({ request }) => {
    await register(request, 'platinum', 'Unlimited-Logics')

    for (let i = 0; i < 6; i++) {
      const res = await request.post('http://localhost:8000/api/logics', {
        data: {
          name: `PlatLogic${i}`,
          description: 'test',
          inputs: [{ name: 'x', type: 'string' }],
          output: 'void',
        },
      })
      expect(res.ok()).toBeTruthy()
    }
  })

  test('self-service subscribe upgrades free user to paid tier', async ({ request }) => {
    const email = `subscribe-self-${Date.now()}@test.dev`
    const regRes = await request.post('http://localhost:8000/api/register', {
      data: {
        name: 'Subscribe Test',
        email,
        password: PASSWORD,
        password_confirmation: PASSWORD,
        tier: 'free',
      },
    })
    expect(regRes.ok()).toBeTruthy()

    const subRes = await request.post('http://localhost:8000/api/subscribe', {
      data: { tier: 'silver', payment_method: 'kbzpay' },
    })
    expect(subRes.ok()).toBeTruthy()
    const subData = await subRes.json()
    expect(subData.data.user.roles.includes('silver')).toBeTruthy()
  })
})
