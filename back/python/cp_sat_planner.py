import json
import sys
from collections import defaultdict

from ortools.sat.python import cp_model


def teacher_status_for_slot(teacher, slot_id):
    for slot in teacher.get("disponibilites_par_creneau", []):
        if slot.get("creneau_id") == slot_id:
            return slot.get("statut", "available")
    return "available"


def build_model(planning_input):
    model = cp_model.CpModel()

    stages = planning_input.get("stages", [])
    slots = planning_input.get("creneaux", [])
    rooms = planning_input.get("salles", [])
    teachers = planning_input.get("enseignants", [])

    stage_by_id = {stage["stage_id"]: stage for stage in stages}
    slot_by_id = {slot["id"]: slot for slot in slots}
    room_by_id = {room["id"]: room for room in rooms}
    teacher_by_id = {teacher["id"]: teacher for teacher in teachers}

    assign = {}
    stage_slot = {}
    juror = {}

    valid_room_ids_by_stage = {}
    valid_juror_ids_by_stage_slot = defaultdict(list)
    teacher_busy_stage_slots = defaultdict(list)
    assignment_preference_terms = []

    for stage in stages:
        stage_id = stage["stage_id"]
        department_id = (((stage.get("etudiant") or {}).get("specialite") or {}).get("departement_id"))
        encadrant_id = ((stage.get("encadrant_academique") or {}).get("id"))
        required_extra = int(stage.get("nombre_jures_a_ajouter_hors_encadrant", 0))

        valid_room_ids = []
        for room in rooms:
            room_department_id = ((room.get("departement") or {}).get("id"))
            if department_id and room_department_id != department_id:
                continue
            valid_room_ids.append(room["id"])

        valid_room_ids_by_stage[stage_id] = valid_room_ids

        for slot in slots:
            slot_id = slot["id"]
            stage_slot[(stage_id, slot_id)] = model.NewBoolVar(f"stage_{stage_id}_slot_{slot_id}")

            if encadrant_id:
                teacher_busy_stage_slots[(encadrant_id, slot_id)].append(stage_slot[(stage_id, slot_id)])

            allowed_juror_ids = []
            for teacher_id in stage.get("jury_candidate_ids", []):
                teacher = teacher_by_id.get(teacher_id)
                if not teacher:
                    continue
                if teacher_status_for_slot(teacher, slot_id) == "unavailable":
                    continue
                allowed_juror_ids.append(teacher_id)

            valid_juror_ids_by_stage_slot[(stage_id, slot_id)] = allowed_juror_ids

            for teacher_id in allowed_juror_ids:
                juror[(stage_id, slot_id, teacher_id)] = model.NewBoolVar(
                    f"juror_{stage_id}_{slot_id}_{teacher_id}"
                )
                teacher_busy_stage_slots[(teacher_id, slot_id)].append(juror[(stage_id, slot_id, teacher_id)])

            if required_extra == 0:
                continue

            if len(allowed_juror_ids) < required_extra:
                model.Add(stage_slot[(stage_id, slot_id)] == 0)
            else:
                model.Add(
                    sum(juror[(stage_id, slot_id, teacher_id)] for teacher_id in allowed_juror_ids)
                    == required_extra * stage_slot[(stage_id, slot_id)]
                )
                for teacher_id in allowed_juror_ids:
                    model.Add(juror[(stage_id, slot_id, teacher_id)] <= stage_slot[(stage_id, slot_id)])

        if encadrant_id and teacher_by_id.get(encadrant_id):
            for slot in slots:
                if teacher_status_for_slot(teacher_by_id[encadrant_id], slot["id"]) == "unavailable":
                    model.Add(stage_slot[(stage_id, slot["id"])] == 0)

    for stage in stages:
        stage_id = stage["stage_id"]
        valid_room_ids = valid_room_ids_by_stage[stage_id]

        for slot in slots:
            slot_id = slot["id"]
            valid_assignments = []

            for room_id in valid_room_ids:
                var = model.NewBoolVar(f"assign_{stage_id}_{slot_id}_{room_id}")
                assign[(stage_id, slot_id, room_id)] = var
                valid_assignments.append(var)

                preference = 0
                encadrant_id = ((stage.get("encadrant_academique") or {}).get("id"))
                if encadrant_id and encadrant_id in teacher_by_id:
                    status = teacher_status_for_slot(teacher_by_id[encadrant_id], slot_id)
                    preference += {"preferred": 4, "available": 2, "avoid": -2}.get(status, 0)

                assignment_preference_terms.append(preference * var)

            if valid_assignments:
                model.Add(sum(valid_assignments) == stage_slot[(stage_id, slot_id)])
            else:
                model.Add(stage_slot[(stage_id, slot_id)] == 0)

        model.Add(sum(stage_slot[(stage_id, slot["id"])] for slot in slots) <= 1)

    for slot in slots:
        slot_id = slot["id"]
        for room in rooms:
            room_id = room["id"]
            room_assignments = [
                assign[(stage["stage_id"], slot_id, room_id)]
                for stage in stages
                if (stage["stage_id"], slot_id, room_id) in assign
            ]
            if room_assignments:
                model.Add(sum(room_assignments) <= 1)

    for teacher_slot_vars in teacher_busy_stage_slots.values():
        if teacher_slot_vars:
            model.Add(sum(teacher_slot_vars) <= 1)

    assignment_terms = [stage_slot[(stage["stage_id"], slot["id"])] for stage in stages for slot in slots]
    model.Maximize(sum(assignment_terms) * 1000 + sum(assignment_preference_terms))

    return model, {
        "stage_by_id": stage_by_id,
        "slot_by_id": slot_by_id,
        "teacher_by_id": teacher_by_id,
        "assign": assign,
        "stage_slot": stage_slot,
        "juror": juror,
        "valid_juror_ids_by_stage_slot": valid_juror_ids_by_stage_slot,
    }


def solve(planning_input, options):
    model, ctx = build_model(planning_input)
    solver = cp_model.CpSolver()
    solver.parameters.max_time_in_seconds = float(options.get("max_time_in_seconds", 20))
    solver.parameters.num_search_workers = int(options.get("num_search_workers", 8))

    status = solver.Solve(model)

    stage_by_id = ctx["stage_by_id"]
    assign = ctx["assign"]
    juror = ctx["juror"]
    valid_juror_ids_by_stage_slot = ctx["valid_juror_ids_by_stage_slot"]

    assignments = []
    assigned_stage_ids = set()

    if status in (cp_model.OPTIMAL, cp_model.FEASIBLE):
        for (stage_id, slot_id, room_id), var in assign.items():
            if solver.BooleanValue(var):
                stage = stage_by_id[stage_id]
                jury_ids = [stage["encadrant_academique"]["id"]]
                selected_jurors = []

                for teacher_id in valid_juror_ids_by_stage_slot[(stage_id, slot_id)]:
                    juror_var = juror.get((stage_id, slot_id, teacher_id))
                    if juror_var is not None and solver.BooleanValue(juror_var):
                        selected_jurors.append(teacher_id)

                jury_ids.extend(selected_jurors)
                assignments.append({
                    "stage_id": stage_id,
                    "creneau_id": slot_id,
                    "salle_id": room_id,
                    "jury_ids": jury_ids,
                })
                assigned_stage_ids.add(stage_id)

    unassigned_stage_ids = [
        stage["stage_id"]
        for stage in planning_input.get("stages", [])
        if stage["stage_id"] not in assigned_stage_ids
    ]

    status_name = {
        cp_model.OPTIMAL: "OPTIMAL",
        cp_model.FEASIBLE: "FEASIBLE",
        cp_model.INFEASIBLE: "INFEASIBLE",
        cp_model.MODEL_INVALID: "MODEL_INVALID",
        cp_model.UNKNOWN: "UNKNOWN",
    }.get(status, str(status))

    return {
        "algorithm": "cp_sat_v1",
        "assignments": assignments,
        "unassigned_stage_ids": unassigned_stage_ids,
        "hard_violations": 0,
        "score": len(assignments) * 1000 - len(unassigned_stage_ids) * 100,
        "meta": {
            "solver_status": status_name,
            "max_time_in_seconds": float(options.get("max_time_in_seconds", 20)),
            "num_search_workers": int(options.get("num_search_workers", 8)),
        },
    }


def main():
    if len(sys.argv) != 3:
        print("Usage: cp_sat_planner.py <input_json> <output_json>", file=sys.stderr)
        return 1

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    with open(input_path, "r", encoding="utf-8") as handle:
        payload = json.load(handle)

    planning_input = payload.get("planning_input", {})
    options = payload.get("options", {})
    result = solve(planning_input, options)

    with open(output_path, "w", encoding="utf-8") as handle:
        json.dump(result, handle, ensure_ascii=False, indent=2)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
