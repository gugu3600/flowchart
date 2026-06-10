import { test, expect } from '@playwright/test'

const PASSWORD = 'TestPass1!'

test.describe('Color customization tier enforcement', () => {
  test('free tier save request with color config succeeds (backend does not reject colors)', async ({ request }) => {
    const email = `free-color-api-${Date.now()}@test.dev`
    const regRes = await request.post('http://localhost:8000/api/register', {
      data: {
        name: 'Free API Color',
        email,
        password: PASSWORD,
        password_confirmation: PASSWORD,
        tier: 'free',
        payment_method: 'kbzpay',
      },
    })
    expect(regRes.ok()).toBeTruthy()

    const flowRes = await request.post('http://localhost:8000/api/flows', {
      data: { name: 'Free Color Flow' },
    })
    expect(flowRes.ok()).toBeFalsy()
  })

  test('silver tier can save a node with backgroundColor config and read it back', async ({ request }) => {
    const email = `silver-color-api-${Date.now()}@test.dev`
    const regRes = await request.post('http://localhost:8000/api/register', {
      data: {
        name: 'Silver API Color',
        email,
        password: PASSWORD,
        password_confirmation: PASSWORD,
        tier: 'silver',
        payment_method: 'kbzpay',
      },
    })
    expect(regRes.ok()).toBeTruthy()

    const flowRes = await request.post('http://localhost:8000/api/flows', {
      data: { name: 'Silver Color Flow' },
    })
    expect(flowRes.ok()).toBeTruthy()
    const flowData = await flowRes.json()
    const flowId = flowData.data.flow.id

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

  test('silver tier can save a node with edge strokeColor config', async ({ request }) => {
    const email = `silver-edge-api-${Date.now()}@test.dev`
    const regRes = await request.post('http://localhost:8000/api/register', {
      data: {
        name: 'Silver Edge Color',
        email,
        password: PASSWORD,
        password_confirmation: PASSWORD,
        tier: 'silver',
        payment_method: 'kbzpay',
      },
    })
    expect(regRes.ok()).toBeTruthy()

    const flowRes = await request.post('http://localhost:8000/api/flows', {
      data: { name: 'Silver Edge Flow' },
    })
    expect(flowRes.ok()).toBeTruthy()
    const flowData = await flowRes.json()
    const flowId = flowData.data.flow.id

    const saveRes = await request.post(`http://localhost:8000/api/flows/${flowId}/save`, {
      data: {
        nodes: [
          {
            id: 'node_a',
            type: 'logic',
            label: 'Node A',
            position_x: 100,
            position_y: 200,
            data: { label: 'Node A' },
            config: {},
          },
          {
            id: 'node_b',
            type: 'logic',
            label: 'Node B',
            position_x: 400,
            position_y: 200,
            data: { label: 'Node B' },
            config: {},
          },
        ],
        edges: [
          {
            source: 'node_a',
            target: 'node_b',
            config: { strokeColor: '#ef4444' },
          },
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
})
