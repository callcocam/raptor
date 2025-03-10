type GridType = number | Array<Record<string, number>> | undefined

export function useGridClasses() {
  const generateGridClasses = (grid: GridType): string => {
    if (!grid) return 'col-span-12'

    if (typeof grid === 'number' || typeof grid === 'string') {
      return `col-span-${grid}`
    }

    if (Array.isArray(grid)) {
      return grid.map(col => `col-span-${col}`).join(' ')
    }

    const classes: string[] = []
    Object.entries(grid).forEach(([key, value]) => { 
      classes.push(`${key}:col-span-${value}`)
    })
    return classes.join(' ')
  }

  const generateLayoutClasses = (layout: GridType): string => {
    if (!layout) return 'grid grid-cols-12 gap-4'

    const classes = ['grid']

    if (typeof layout === 'number') {
      classes.push(`grid-cols-${layout}`)
    } else if (Array.isArray(layout)) {
      layout.forEach(breakpoint => {
        const [key, value] = Object.entries(breakpoint)[0]
        classes.push(`${key}:grid-cols-${value}`)
      })
    } else {
      classes.push('grid-cols-12')
    }

    classes.push('gap-4')
    return classes.join(' ')
  }

  return {
    generateGridClasses,
    generateLayoutClasses
  }
}
