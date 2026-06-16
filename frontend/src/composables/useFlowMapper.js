export function useFlowMapper() {
  function toClientNode(n) {
    const nc = n.config || {}
    return {
      id: String(n.id),
      type: n.type,
      label: n.label || (n.data?.label) || '',
      position: { x: n.position_x, y: n.position_y },
      data: { label: n.label, ...(n.data || {}) },
      style: nc.backgroundColor ? { background: nc.backgroundColor } : {},
      config: nc,
    }
  }

  function toClientEdge(e) {
    const ec = e.config || {}
    return {
      id: `e-${e.id}`,
      source: String(e.source_node_id),
      target: String(e.target_node_id),
      label: e.label || '',
      style: ec.strokeColor ? { stroke: ec.strokeColor } : {},
      config: ec,
    }
  }

  function toServerNode(n) {
    return {
      id: n.id,
      type: n.type,
      label: n.data?.label || '',
      position_x: n.position.x,
      position_y: n.position.y,
      data: n.data || {},
      config: n.config || {},
    }
  }

  function toServerEdge(e) {
    return {
      source: e.source,
      target: e.target,
      label: e.label || '',
      config: e.config || {},
    }
  }

  function filterByMode(nodes, mode) {
    if (mode === 'flow') return nodes.filter(n => n.type === 'logic' || n.type === 'folderFile')
    return nodes.filter(n => n.type === 'table')
  }

  return { toClientNode, toClientEdge, toServerNode, toServerEdge, filterByMode }
}
