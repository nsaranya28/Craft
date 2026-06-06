
import * as THREE from 'https://cdn.skypack.dev/three@0.132.2';

const container = document.getElementById('canvas-container');
if (container) {
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });

    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
    scene.add(ambientLight);

    const pointLight = new THREE.PointLight(0xffffff, 0.8);
    pointLight.position.set(5, 5, 5);
    scene.add(pointLight);

    const secondaryLight = new THREE.PointLight(0xffb3ba, 0.6);
    secondaryLight.position.set(-5, -5, 2);
    scene.add(secondaryLight);

    // Objects - Geometric "Craft" shapes
    const group = new THREE.Group();
    scene.add(group);

    const geometries = [
        new THREE.SphereGeometry(1, 32, 32),
        new THREE.TorusGeometry(0.8, 0.3, 16, 100),
        new THREE.CylinderGeometry(0.6, 0.6, 1.2, 32)
    ];

    const materials = [
        new THREE.MeshStandardMaterial({ color: 0xffb3ba, roughness: 0.6, metalness: 0.1 }), // Pastel Pink
        new THREE.MeshStandardMaterial({ color: 0xffdfba, roughness: 0.6, metalness: 0.1 }), // Pastel Peach
        new THREE.MeshStandardMaterial({ color: 0xffffba, roughness: 0.6, metalness: 0.1 }), // Pastel Yellow
        new THREE.MeshStandardMaterial({ color: 0xbaffc9, roughness: 0.6, metalness: 0.1 }), // Pastel Green
        new THREE.MeshStandardMaterial({ color: 0xbae1ff, roughness: 0.6, metalness: 0.1 })  // Pastel Blue
    ];

    const meshes = [];
    for (let i = 0; i < 30; i++) {
        const geo = geometries[Math.floor(Math.random() * geometries.length)];
        const mat = materials[Math.floor(Math.random() * materials.length)];
        const mesh = new THREE.Mesh(geo, mat);

        const scale = Math.random() * 0.3 + 0.1;
        mesh.scale.set(scale, scale, scale);

        mesh.position.set(
            (Math.random() - 0.5) * 10,
            (Math.random() - 0.5) * 10,
            (Math.random() - 0.5) * 5
        );

        mesh.rotation.set(
            Math.random() * Math.PI,
            Math.random() * Math.PI,
            Math.random() * Math.PI
        );

        group.add(mesh);
        meshes.push({
            mesh,
            speedX: (Math.random() - 0.5) * 0.01,
            speedY: (Math.random() - 0.5) * 0.01,
            rotSpeed: (Math.random() - 0.5) * 0.02
        });
    }

    camera.position.z = 6;

    // Mouse Interaction
    let mouseX = 0;
    let mouseY = 0;
    document.addEventListener('mousemove', (event) => {
        mouseX = (event.clientX / window.innerWidth) - 0.5;
        mouseY = (event.clientY / window.innerHeight) - 0.5;
    });

    // Animation Loop
    function animate() {
        requestAnimationFrame(animate);

        meshes.forEach(m => {
            m.mesh.rotation.x += m.rotSpeed;
            m.mesh.rotation.y += m.rotSpeed;
            m.mesh.position.y += Math.sin(Date.now() * 0.001 + m.mesh.position.x) * 0.002;
        });

        group.rotation.y += (mouseX * 0.5 - group.rotation.y) * 0.05;
        group.rotation.x += (mouseY * 0.5 - group.rotation.x) * 0.05;

        renderer.render(scene, camera);
    }

    animate();

    // Handle Resize
    window.addEventListener('resize', () => {
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    });
}
